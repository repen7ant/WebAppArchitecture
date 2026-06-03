import asyncio
import json
import os
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from redis import asyncio as aioredis

from database import get_db
from routers import comments, ws


async def handle_user_renamed(data: dict):
    # Денормализация: обновляем сохранённое имя автора во всех его комментариях.
    conn = await get_db()
    async with conn.cursor() as cur:
        await cur.execute(
            "UPDATE comments SET author_name=%s WHERE author_id=%s",
            (data["new_name"], data["id"]),
        )
        await conn.commit()
    conn.close()


async def redis_subscriber():
    redis = await aioredis.from_url(
        f"redis://{os.environ.get('REDIS_HOST', '127.0.0.1')}:6379"
    )
    pubsub = redis.pubsub()
    await pubsub.subscribe("new_post", "user.renamed")
    print("Redis subscriber started: channels new_post, user.renamed", flush=True)

    async for message in pubsub.listen():
        if message["type"] != "message":
            continue
        channel = message["channel"]
        if isinstance(channel, bytes):
            channel = channel.decode()
        data = json.loads(message["data"])

        if channel == "new_post":
            await ws.manager.broadcast({"type": "new_post", "post": data})
        elif channel == "user.renamed":
            await handle_user_renamed(data)
            await ws.manager.broadcast({
                "type": "user_renamed",
                "user_id": data["id"],
                "new_name": data["new_name"],
            })


@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(redis_subscriber())
    yield
    task.cancel()


app = FastAPI(title="Boardy API", version="0.5.0", lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://boardy.emrysdev.xyz",
        "http://localhost",
        "http://localhost:80",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(comments.router)
app.include_router(ws.router)
