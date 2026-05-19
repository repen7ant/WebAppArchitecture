from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from routers import comments, ws

app = FastAPI(title="Boardy API", version="0.2.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://boardy.emrysdev.xyz"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(comments.router)
app.include_router(ws.router)


@app.post("/internal/broadcast")
async def internal_broadcast(request: Request):
    data = await request.json()
    await ws.manager.broadcast({"type": "new_post", "post": data})
    return {"ok": True}
