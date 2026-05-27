FROM python:3.11-slim

WORKDIR /app

# System dependencies for cryptography (PyJWT RS256)
RUN apt-get update && apt-get install -y \
    build-essential libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Requirements first — cached layer
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# App code
COPY . .

EXPOSE 8000
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
