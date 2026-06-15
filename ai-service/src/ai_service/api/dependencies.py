"""FastAPI dependency providers for settings and services."""

from functools import lru_cache

from ai_service.config.settings import Settings
from ai_service.repositories.postgres_tool_result_repository import PostgresToolResultRepository
from ai_service.services.job_service import JobService
from ai_service.services.processor_factory import resolve_job_processor


@lru_cache
def get_settings() -> Settings:
    """Return cached application settings.

    Returns:
        Parsed ``Settings`` instance loaded from environment variables.
    """
    return Settings()


def get_job_service() -> JobService:
    """Construct a job service wired to PostgreSQL and the stub processor.

    Returns:
        JobService ready to enqueue and process AI jobs.
    """
    settings = get_settings()
    repository = PostgresToolResultRepository(settings.database_url)
    processor = resolve_job_processor(repository, settings)
    return JobService(processor)
