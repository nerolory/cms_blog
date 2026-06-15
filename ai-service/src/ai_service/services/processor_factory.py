"""Фабрика: выбор LLM-адаптера и job processor по настройкам приложения."""

from ai_service.config.settings import Settings
from ai_service.repositories.tool_result_repository import ToolResultRepository
from ai_service.services.job_processor import JobProcessor, LlmJobProcessor
from ai_service.services.llm_adapter import LlmAdapter, OpenAiCompatibleAdapter, StubLlmAdapter


def resolve_llm_adapter(settings: Settings) -> LlmAdapter:
    """Возвращает OpenAI-адаптер при наличии ключа, иначе заглушку.

    Args:
        settings: Настройки с ``openai_api_key``, base URL и именем модели.

    Returns:
        Реализация ``LlmAdapter`` для текущего окружения.
    """
    if settings.openai_api_key:
        return OpenAiCompatibleAdapter(
            api_key=settings.openai_api_key,
            base_url=settings.openai_base_url,
            model=settings.openai_model,
        )

    return StubLlmAdapter()


def resolve_job_processor(
    repository: ToolResultRepository,
    settings: Settings,
) -> JobProcessor:
    """Собирает ``LlmJobProcessor`` с репозиторием и выбранным LLM.

    Args:
        repository: Хранилище результатов AI-инструментов.
        settings: Настройки для выбора LLM-адаптера.

    Returns:
        Готовый процессор для ``JobService``.
    """
    return LlmJobProcessor(repository, resolve_llm_adapter(settings))
