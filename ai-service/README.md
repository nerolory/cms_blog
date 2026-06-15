# AI service (FastAPI)

Scaffold for Python AI worker. Dispatches jobs via `POST /v1/jobs` and persists results to `ai_tool_results`.

## Local dev

```bash
cd ai-service
pip install -e ".[dev]"
export DATABASE_URL=postgresql://laravel:secret@localhost:5432/laravel
uvicorn ai_service.main:app --reload --port 8100
pytest
```

## Docker

```bash
docker compose --profile ai up -d ai-service
curl http://localhost:8100/health
```
