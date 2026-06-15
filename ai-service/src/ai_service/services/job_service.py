"""Job orchestration service."""

from ai_service.domain.models.job_request import JobRequest
from ai_service.domain.models.job_response import JobResponse
from ai_service.services.job_processor import JobProcessor


class JobService:
    """Facade that delegates job processing to a configured processor.

    Args:
        processor: Implementation that executes the job and persists results.
    """

    def __init__(self, processor: JobProcessor) -> None:
        self._processor = processor

    def enqueue(self, job: JobRequest) -> JobResponse:
        """Process a job synchronously and return the outcome.

        Args:
            job: Validated request describing the tool, subject, and payload.

        Returns:
            JobResponse with identifiers and completion status.
        """
        return self._processor.process(job)
