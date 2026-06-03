import aiomysql
from auth import get_current_user
from database import get_db
from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel, Field

from routers.ws import manager

router = APIRouter(prefix="/api")


class CommentIn(BaseModel):
    body: str = Field(..., min_length=1, max_length=2000)
    author_name: str = Field(..., min_length=1, max_length=255)


class CommentUpdate(BaseModel):
    body: str = Field(..., min_length=1, max_length=2000)


@router.get("/posts/{post_id}/comments")
async def list_comments(post_id: int):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute(
            "SELECT id, post_id, author_id, author_name, body, created_at "
            "FROM comments WHERE post_id=%s ORDER BY created_at",
            (post_id,),
        )
        items = await cur.fetchall()
    conn.close()
    for item in items:
        if item.get("created_at"):
            item["created_at"] = str(item["created_at"])
    return {"items": items, "count": len(items)}


@router.post("/posts/{post_id}/comments", status_code=201)
async def create_comment(
    post_id: int, data: CommentIn, user=Depends(get_current_user)
):
    author_id = int(user["sub"])
    conn = await get_db()
    async with conn.cursor() as cur:
        await cur.execute(
            "INSERT INTO comments (post_id, author_id, author_name, body) "
            "VALUES (%s, %s, %s, %s)",
            (post_id, author_id, data.author_name, data.body),
        )
        await conn.commit()
        new_id = cur.lastrowid
    conn.close()

    comment = {
        "id": new_id,
        "post_id": post_id,
        "author_id": author_id,
        "author_name": data.author_name,
        "body": data.body,
    }
    await manager.broadcast({"type": "new_comment", "comment": comment})
    return comment


@router.put("/comments/{comment_id}")
async def update_comment(
    comment_id: int, data: CommentUpdate, user=Depends(get_current_user)
):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute(
            "SELECT author_id FROM comments WHERE id=%s", (comment_id,)
        )
        existing = await cur.fetchone()
        if not existing:
            conn.close()
            raise HTTPException(status_code=404, detail="Комментарий не найден")
        if existing["author_id"] != int(user["sub"]):
            conn.close()
            raise HTTPException(status_code=403, detail="Это не ваш комментарий")
        await cur.execute(
            "UPDATE comments SET body=%s WHERE id=%s", (data.body, comment_id)
        )
        await conn.commit()
    conn.close()

    comment = {"id": comment_id, "body": data.body}
    await manager.broadcast({"type": "update_comment", "comment": comment})
    return comment


@router.delete("/comments/{comment_id}")
async def delete_comment(comment_id: int, user=Depends(get_current_user)):
    conn = await get_db()
    async with conn.cursor(aiomysql.DictCursor) as cur:
        await cur.execute(
            "SELECT author_id FROM comments WHERE id=%s", (comment_id,)
        )
        existing = await cur.fetchone()
        if not existing:
            conn.close()
            raise HTTPException(status_code=404, detail="Комментарий не найден")
        if existing["author_id"] != int(user["sub"]):
            conn.close()
            raise HTTPException(status_code=403, detail="Это не ваш комментарий")
        await cur.execute("DELETE FROM comments WHERE id=%s", (comment_id,))
        await conn.commit()
    conn.close()

    await manager.broadcast({"type": "delete_comment", "comment_id": comment_id})
    return {"ok": True}
