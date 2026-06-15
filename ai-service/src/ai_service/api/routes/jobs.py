"""AI job submission HTTP routes."""

from fastapi import APIRouter, Depends

from ai_service.api.dependencies import get_job_service
from ai_service.domain.models.job_request import JobRequest
from ai_service.domain.models.job_response import JobResponse
from ai_service.services.job_service import JobService


class JobsRouter:
    """Registers and handles AI job creation endpoints."""

    def __init__(self) -> None:
        self.router = APIRouter(prefix="/v1", tags=["jobs"])
        self.router.add_api_route("/jobs", self.create_job, methods=["POST"])

    def create_job(
        self,
        request: JobRequest,
        job_service: JobService = Depends(get_job_service),
    ) -> JobResponse:
        """Accept a job request and enqueue it for processing.

        Args:
            request: Validated job payload (tool, subject, and input data).
            job_service: Service that delegates processing to the configured processor.

        Returns:
            JobResponse with job identifier, status, and persisted tool result id.
        """
        return job_service.enqueue(request)
