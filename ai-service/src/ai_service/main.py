"""FastAPI application factory and ASGI entry point."""

from fastapi import Depends, FastAPI

from ai_service.api.dependencies import get_settings
from ai_service.api.routes.health import HealthRouter
from ai_service.api.routes.jobs import JobsRouter
from ai_service.config.settings import Settings


def create_app() -> FastAPI:
    """Build and configure the FastAPI application.

    Returns:
        Configured FastAPI instance with health, job, and root routes.
    """
    settings = get_settings()

    app = FastAPI(title=settings.app_name)
    app.include_router(HealthRouter(settings).router)
    app.include_router(JobsRouter().router)

    @app.get("/")
    def root(settings: Settings = Depends(get_settings)) -> dict[str, str]:
        """Return service metadata and documentation link.

        Args:
            settings: Application settings injected by FastAPI.

        Returns:
            Mapping with service name and OpenAPI docs path.
        """
        return {"service": settings.app_name, "docs": "/docs"}

    return app


app = create_app()
