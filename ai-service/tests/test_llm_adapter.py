"""Tests for LLM adapter resolution and job processor wiring."""

from ai_service.config.settings import Settings
from ai_service.services.job_processor import LlmJobProcessor
from ai_service.services.llm_adapter import OpenAiCompatibleAdapter, StubLlmAdapter
from ai_service.services.processor_factory import resolve_job_processor, resolve_llm_adapter


class FakeRepository:
    pass


def test_resolve_llm_adapter_falls_back_to_stub_without_api_key() -> None:
    settings = Settings(openai_api_key=None)
    adapter = resolve_llm_adapter(settings)
    assert isinstance(adapter, StubLlmAdapter)
    assert "Stub AI result" in adapter.complete("hello")


def test_resolve_llm_adapter_uses_openai_when_api_key_present() -> None:
    settings = Settings(openai_api_key="test-key")
    adapter = resolve_llm_adapter(settings)
    assert isinstance(adapter, OpenAiCompatibleAdapter)


def test_resolve_job_processor_returns_llm_job_processor() -> None:
    settings = Settings(openai_api_key=None)
    processor = resolve_job_processor(FakeRepository(), settings)
    assert isinstance(processor, LlmJobProcessor)
