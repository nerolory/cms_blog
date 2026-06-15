"""Application configuration loaded from environment variables."""

from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """Runtime settings for the AI service.

    Attributes:
        app_name: Human-readable service identifier returned by health checks.
        database_url: PostgreSQL connection string for persisting tool results.
        log_level: Logging verbosity (e.g. ``info``, ``debug``).
    """

    model_config = SettingsConfigDict(
        env_file=".env",
        extra="ignore",
        populate_by_name=True,
    )

    app_name: str = "demo-ai-service"
    database_url: str = Field(
        default="postgresql://laravel:secret@database:5432/laravel",
        validation_alias="DATABASE_URL",
    )
    log_level: str = "info"
    openai_api_key: str | None = Field(default=None, validation_alias="OPENAI_API_KEY")
    openai_base_url: str = Field(
        default="https://api.openai.com/v1",
        validation_alias="OPENAI_BASE_URL",
    )
    openai_model: str = Field(default="gpt-4o-mini", validation_alias="OPENAI_MODEL")
