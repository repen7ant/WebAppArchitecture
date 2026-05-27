import os

import aiomysql

DB_CONFIG = {
    "host": os.environ.get("DB_HOST", "127.0.0.1"),
    "port": int(os.environ.get("DB_PORT", "3306")),
    "user": os.environ.get("DB_USER", "boardy"),
    "password": os.environ.get("DB_PASSWORD", ""),
    "db": os.environ.get("DB_NAME", "boardy_api"),
    "charset": "utf8mb4",
}


async def get_db():
    return await aiomysql.connect(**DB_CONFIG)
