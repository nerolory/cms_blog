"""Адаптеры LLM: протокол и реализации (заглушка и OpenAI-совместимый API)."""

from typing import Protocol


class LlmAdapter(Protocol):
    """Контракт синхронного вызова языковой модели по текстовому промпту."""

    def complete(self, prompt: str) -> str:
        """Возвращает текстовый ответ модели на заданный промпт.

        Args:
            prompt: Текст запроса к модели.

        Returns:
            Сгенерированный текст ответа.
        """
        ...


class StubLlmAdapter:
    """Заглушка LLM для локальной разработки без API-ключа."""

    def complete(self, prompt: str) -> str:
        """Возвращает фиксированный демо-ответ без обращения к внешнему API.

        Args:
            prompt: Текст запроса (не используется в заглушке).

        Returns:
            Сообщение о том, что ключ API не настроен.
        """
        return "Stub AI result (no API key configured)."


class OpenAiCompatibleAdapter:
    """Клиент OpenAI-совместимого chat/completions API через httpx."""

    def __init__(
        self,
        *,
        api_key: str,
        base_url: str,
        model: str,
        timeout_seconds: float = 60.0,
    ) -> None:
        """Создаёт HTTP-клиент с заданными учётными данными и моделью.

        Args:
            api_key: Bearer-токен провайдера.
            base_url: Базовый URL API (без завершающего слэша).
            model: Идентификатор модели в запросе chat/completions.
            timeout_seconds: Таймаут HTTP-запроса в секундах.
        """
        import httpx

        self._api_key = api_key
        self._base_url = base_url.rstrip("/")
        self._model = model
        self._client = httpx.Client(timeout=timeout_seconds)

    def complete(self, prompt: str) -> str:
        """Отправляет промпт в chat/completions и возвращает content ответа.

        Args:
            prompt: Текст пользовательского сообщения.

        Returns:
            Текст первого choice из ответа API.

        Raises:
            httpx.HTTPStatusError: При неуспешном HTTP-статусе от провайдера.
        """
        response = self._client.post(
            f"{self._base_url}/chat/completions",
            headers={
                "Authorization": f"Bearer {self._api_key}",
                "Content-Type": "application/json",
            },
            json={
                "model": self._model,
                "messages": [{"role": "user", "content": prompt}],
            },
        )
        response.raise_for_status()
        payload = response.json()
        return str(payload["choices"][0]["message"]["content"])

    def close(self) -> None:
        """Закрывает underlying HTTP-клиент и освобождает соединения."""
        self._client.close()
