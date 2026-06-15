"""Job submission response schema."""

from pydantic import BaseModel


class JobResponse(BaseModel):
    """Outcome returned after a job is accepted and processed.

    Attributes:
        job_id: Unique identifier assigned to the job run.
        status: Processing status (e.g. ``completed``).
        tool_result_id: Primary key of the persisted tool result, if any.
    """

    job_id: str
    status: str
    tool_result_id: int | None = None
