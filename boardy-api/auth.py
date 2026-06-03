import os

import jwt
from fastapi import Header, HTTPException

# In Docker: key lives in laravel_storage volume, path passed via OAUTH_PUBLIC_KEY env.
# Fallback: local file for non-Docker runs.
PUBLIC_KEY_PATH = os.environ.get(
    "OAUTH_PUBLIC_KEY",
    os.path.join(os.path.dirname(__file__), "oauth-public.key"),
)

_PUBLIC_KEY: str | None = None


def _load_key() -> str:
    """Lazy-load the public key so FastAPI starts even before passport:install."""
    global _PUBLIC_KEY
    if _PUBLIC_KEY is None:
        with open(PUBLIC_KEY_PATH) as f:
            _PUBLIC_KEY = f.read()
    return _PUBLIC_KEY


async def get_current_user(authorization: str = Header(None)):
    if not authorization or not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Token required")
    token = authorization.split(" ")[1]
    try:
        payload = jwt.decode(
            token,
            _load_key(),
            algorithms=["RS256"],
            options={"verify_aud": False},
        )
        # payload: sub (user_id), aud (client_id), jti, iat, nbf, exp, scopes
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expired")
    except jwt.InvalidTokenError as e:
        raise HTTPException(status_code=401, detail=f"Invalid token: {e}")
