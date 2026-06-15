"""Repository protocol for AI tool result persistence."""

from typing import Any, Protocol


class ToolResultRecord(Protocol):
    """Minimal persisted tool result shape returned by repositories."""

    id: int


class ToolResultRepository(Protocol):
    """Contract for storing completed AI tool results."""

    def upsert_completed(
        self,
        *,
        subject_type: str,
        subject_id: int,
        tool_code: str,
        payload: dict[str, Any],
        metadata: dict[str, Any] | None,
    ) -> ToolResultRecord:
        """Insert or update a completed tool result row.

        Args:
            subject_type: Fully qualified model class name of the subject entity.
            subject_id: Primary key of the subject entity.
            tool_code: Identifier of the AI tool that produced the result.
            payload: Structured result data to store as JSON.
            metadata: Optional auxiliary metadata stored alongside the payload.

        Returns:
            Record containing the persisted row id.
        """
        ...
