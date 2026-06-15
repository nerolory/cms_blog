"""Health-check response schema."""

from pydantic import BaseModel


class HealthResponse(BaseModel):
    """Payload returned by the ``GET /health`` endpoint.

    Attributes:
        status: Liveness indicator (typically ``ok``).
        service: Configured application name.
    """

    status: str = "ok"
    service: str
