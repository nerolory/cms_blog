"""PostgreSQL-backed persistence for ``ai_tool_results`` rows."""

import json
from datetime import UTC, datetime
from typing import Any

import psycopg
from psycopg.rows import dict_row

from ai_service.repositories.tool_result_repository import ToolResultRecord


class PostgresToolResultRepository:
    """Upserts completed tool results into the ``ai_tool_results`` table.

    Args:
        database_url: PostgreSQL connection string.
    """

    def __init__(self, database_url: str) -> None:
        self._database_url = database_url

    def upsert_completed(
        self,
        *,
        subject_type: str,
        subject_id: int,
        tool_code: str,
        payload: dict[str, Any],
        metadata: dict[str, Any] | None,
    ) -> ToolResultRecord:
        """Insert or update a completed result keyed by subject and tool code.

        Args:
            subject_type: Fully qualified model class name of the subject entity.
            subject_id: Primary key of the subject entity.
            tool_code: Identifier of the AI tool that produced the result.
            payload: Structured result data serialized to JSON.
            metadata: Optional auxiliary metadata serialized to JSON.

        Returns:
            Lightweight record wrapper with the persisted row id.

        Raises:
            RuntimeError: If the database does not return a row after upsert.
        """
        now = datetime.now(UTC)
        with psycopg.connect(self._database_url, row_factory=dict_row) as connection:
            with connection.cursor() as cursor:
                cursor.execute(
                    """
                    INSERT INTO ai_tool_results (
                        subject_type,
                        subject_id,
                        tool_code,
                        status,
                        payload,
                        metadata,
                        completed_at,
                        created_at,
                        updated_at
                    )
                    VALUES (%s, %s, %s, %s, %s::json, %s::json, %s, %s, %s)
                    ON CONFLICT (subject_type, subject_id, tool_code)
                    DO UPDATE SET
                        status = EXCLUDED.status,
                        payload = EXCLUDED.payload,
                        metadata = EXCLUDED.metadata,
                        completed_at = EXCLUDED.completed_at,
                        updated_at = EXCLUDED.updated_at
                    RETURNING id
                    """,
                    (
                        subject_type,
                        subject_id,
                        tool_code,
                        "completed",
                        json.dumps(payload),
                        json.dumps(metadata) if metadata is not None else None,
                        now,
                        now,
                        now,
                    ),
                )
                row = cursor.fetchone()
            connection.commit()

        if row is None:
            raise RuntimeError("Failed to persist ai_tool_results row.")

        return _ToolResultRow(id=int(row["id"]))


class _ToolResultRow:
    """Internal adapter exposing only the persisted row id.

    Args:
        id: Primary key of the ``ai_tool_results`` row.
    """

    def __init__(self, id: int) -> None:
        self.id = id
