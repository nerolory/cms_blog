"""Обработчики AI-задач: протокол и реализация через LLM."""

from typing import Any, Protocol

from ai_service.domain.models.job_request import JobRequest
from ai_service.domain.models.job_response import JobResponse
from ai_service.services.llm_adapter import LlmAdapter


class JobProcessor(Protocol):
    """Контракт синхронной обработки входящей AI-задачи."""

    def process(self, job: JobRequest) -> JobResponse:
        """Выполняет задачу и возвращает идентификаторы результата.

        Args:
            job: Валидированный запрос с tool_code, subject и payload.

        Returns:
            Ответ с job_id, статусом и ссылкой на сохранённый tool result.
        """
        ...


class LlmJobProcessor:
    """Процессор: формирует промпт, вызывает LLM и сохраняет результат в БД."""

    def __init__(self, repository: Any, llm_adapter: LlmAdapter) -> None:
        """Связывает репозиторий результатов с адаптером LLM.

        Args:
            repository: Репозиторий с методом ``upsert_completed``.
            llm_adapter: Реализация вызова языковой модели.
        """
        self._repository = repository
        self._llm_adapter = llm_adapter

    def process(self, job: JobRequest) -> JobResponse:
        """Суммаризирует payload через LLM и upsert-ит строку в ai_tool_results.

        Args:
            job: Входящая задача с типом субъекта, id и JSON payload.

        Returns:
            JobResponse со статусом ``completed`` и id сохранённой записи.
        """
        import json
        import uuid

        job_id = str(uuid.uuid4())
        prompt = (
            "Summarize the following AI tool input as concise JSON with keys "
            "'summary' and 'highlights'.\n"
            f"Tool: {job.tool_code}\n"
            f"Input: {json.dumps(job.payload, ensure_ascii=False)}"
        )
        summary = self._llm_adapter.complete(prompt)
        result_payload: dict[str, Any] = {
            "summary": summary,
            "tool_code": job.tool_code,
            "input": job.payload,
        }
        metadata = job.metadata or {}
        metadata["job_id"] = job_id
        metadata["processor"] = type(self._llm_adapter).__name__

        record = self._repository.upsert_completed(
            subject_type=job.subject_type,
            subject_id=job.subject_id,
            tool_code=job.tool_code,
            payload=result_payload,
            metadata=metadata,
        )

        return JobResponse(
            job_id=job_id,
            status="completed",
            tool_result_id=record.id,
        )
