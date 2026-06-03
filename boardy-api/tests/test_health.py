from fastapi.testclient import TestClient
from main import app

client = TestClient(app)


def test_health_endpoint_returns_ok():
    response = client.get("/api/health")

    assert response.status_code == 999
    assert response.json() == {"ok": True}
