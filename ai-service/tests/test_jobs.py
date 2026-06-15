"""Integration tests for job creation and request validation."""

from typing import Any

import pytest
from fastapi.testclient import TestClient
from pydantic import ValidationError

from ai_service.api.dependencies import get_job_service
from ai_service.domain.models.job_request import JobRequest
from ai_service.main import create_app
from ai_service.services.job_processor import LlmJobProcessor
from ai_service.services.job_service import JobService
from ai_service.services.llm_adapter import StubLlmAdapter


class FakeToolResultRepository:
    """In-memory repository that records upsert calls for assertions."""

    def __init__(self) -> None:
        self.calls: list[dict[str, Any]] = []

    def upsert_completed(
        self,
        *,
        subject_type: str,
        subject_id: int,
        tool_code: str,
        payload: dict[str, Any],
        metadata: dict[str, Any] | None,
    ) -> Any:
        """Record arguments and return a stub record with id 42.

        Args:
            subject_type: Subject model class name passed to the processor.
            subject_id: Subject entity primary key.
            tool_code: Tool identifier from the job request.
            payload: Result payload that would be persisted.
            metadata: Optional metadata accompanying the result.

        Returns:
            Object with ``id`` attribute set to 42.
        """
        self.calls.append(
            {
                "subject_type": subject_type,
                "subject_id": subject_id,
                "tool_code": tool_code,
                "payload": payload,
                "metadata": metadata,
            }
        )

        class Record:
            id = 42

        return Record()


@pytest.fixture
def client() -> TestClient:
    """FastAPI test client with job service backed by a fake repository.

    Returns:
        TestClient with ``get_job_service`` overridden for isolation.
    """
    repository = FakeToolResultRepository()
    job_service = JobService(LlmJobProcessor(repository, StubLlmAdapter()))
    app = create_app()
    app.dependency_overrides[get_job_service] = lambda: job_service
    return TestClient(app)


def test_create_job_persists_via_repository(client: TestClient) -> None:
    """POST /v1/jobs returns completed status and repository tool_result_id."""
    response = client.post(
        "/v1/jobs",
        json={
            "tool_code": "comment_summary",
            "subject_type": "App\\Models\\Post",
            "subject_id": 7,
            "payload": {"comment_count": 3},
            "metadata": {"source": "pytest"},
        },
    )

    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "completed"
    assert body["tool_result_id"] == 42
    assert "job_id" in body


def test_job_request_validation() -> None:
    """JobRequest rejects empty tool_code with ValidationError."""
    with pytest.raises(ValidationError):
        JobRequest(tool_code="", subject_type="App\\Models\\Post", subject_id=1)
