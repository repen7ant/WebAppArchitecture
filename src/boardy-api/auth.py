import os

import jwt
from fastapi import Header, HTTPException

PUBLIC_KEY_PATH = os.path.join(os.path.dirname(__file__), "oauth-public.key")
with open(PUBLIC_KEY_PATH) as f:
    PUBLIC_KEY = f.read()


async def get_current_user(authorization: str = Header(None)):
    if not authorization or not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Token required")
    token = authorization.split(" ")[1]
    try:
        payload = jwt.decode(
            token,
            PUBLIC_KEY,
            algorithms=["RS256"],
            options={"verify_aud": False},
        )
        # payload: sub (user_id), aud (client_id), jti, iat, nbf, exp, scopes
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expired")
    except jwt.InvalidTokenError as e:
        raise HTTPException(status_code=401, detail=f"Invalid token: {e}")
