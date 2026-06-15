"""Health-check HTTP routes."""

from fastapi import APIRouter, Depends

from ai_service.domain.models.health_response import HealthResponse
from ai_service.config.settings import Settings


class HealthRouter:
    """Registers and handles the service health endpoint.

    Args:
        settings: Application settings used to populate the response payload.
    """

    def __init__(self, settings: Settings) -> None:
        self._settings = settings
        self.router = APIRouter(tags=["health"])
        self.router.add_api_route("/health", self.health, methods=["GET"])

    def health(self) -> HealthResponse:
        """Return liveness information for load balancers and monitors.

        Returns:
            HealthResponse with status ``ok`` and the configured service name.
        """
        return HealthResponse(status="ok", service=self._settings.app_name)


def get_health_router(settings: Settings = Depends()) -> APIRouter:
    """FastAPI dependency that exposes the health router.

    Args:
        settings: Application settings injected by FastAPI.

    Returns:
        APIRouter with the ``GET /health`` route registered.
    """
    return HealthRouter(settings).router
