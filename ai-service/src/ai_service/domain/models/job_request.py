"""Job submission request schema."""

from typing import Any

from pydantic import BaseModel, Field


class JobRequest(BaseModel):
    """Validated input for creating an AI processing job.

    Attributes:
        tool_code: Non-empty identifier of the AI tool to invoke.
        subject_type: Non-empty fully qualified class name of the subject model.
        subject_id: Positive integer primary key of the subject entity.
        payload: Arbitrary tool input serialized as JSON.
        metadata: Optional caller-provided metadata stored with the result.
    """

    tool_code: str = Field(min_length=1)
    subject_type: str = Field(min_length=1)
    subject_id: int = Field(gt=0)
    payload: dict[str, Any] = Field(default_factory=dict)
    metadata: dict[str, Any] | None = None
