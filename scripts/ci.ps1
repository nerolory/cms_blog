# Полный прогон проверок (Windows).
# PHP — в Docker, frontend — на хосте (node_modules не совместимы с Linux в volume).

$ErrorActionPreference = "Stop"
Set-Location (Split-Path $PSScriptRoot -Parent)

function Invoke-Step {
    param([string]$Label, [scriptblock]$Command)
    Write-Host "==> $Label" -ForegroundColor Cyan
    & $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $Label (exit $LASTEXITCODE)"
    }
}

Invoke-Step "Frontend check (host)" { npm run check:frontend }
Invoke-Step "Frontend build (host)" { npm run build }
Invoke-Step "PHP (Docker)" { docker compose exec -T php composer check:php }
Invoke-Step "Python (ai-service)" { Push-Location ai-service; pip install -e ".[dev]" -q; ruff check .; pytest; Pop-Location }

Write-Host "==> CI passed" -ForegroundColor Green
