"""Integration tests for the health HTTP endpoint."""

from fastapi.testclient import TestClient

from ai_service.main import create_app


def test_health_endpoint() -> None:
    """Health endpoint returns 200 with service name and ok status."""
    client = TestClient(create_app())
    response = client.get("/health")

    assert response.status_code == 200
    assert response.json() == {"status": "ok", "service": "demo-ai-service"}
