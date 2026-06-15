# init-structure.ps1
$folders = @(
    "backend/php",
    "backend/redis",
    "backend/postgresql",
    "backend/nginx"
)
foreach ($f in $folders) {
    New-Item -ItemType Directory -Path $f -Force | Out-Null
}

$rootFiles = @("docker-compose.yml", ".env", "nginx.conf", ".gitignore", ".dockerignore")
foreach ($file in $rootFiles) {
    if (-not (Test-Path $file)) { New-Item -ItemType File -Path $file | Out-Null }
}

foreach ($f in $folders) {
    $df = Join-Path $f "Dockerfile"
    if (-not (Test-Path $df)) { New-Item -ItemType File -Path $df | Out-Null }
}

Write-Host "Structure initialized successfully!" -ForegroundColor Green