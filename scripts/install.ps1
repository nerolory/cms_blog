# One-click native install (Windows PowerShell).
# Prerequisites: PHP 8.3+, Composer, Node 20+

$ErrorActionPreference = "Stop"
Set-Location (Join-Path $PSScriptRoot "..")

function Require-Command([string]$Name) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "$Name not found in PATH"
    }
}

Write-Host "==> Checking prerequisites"
Require-Command php
Require-Command composer
Require-Command node
Require-Command npm

$phpVersion = php -r "echo PHP_VERSION;"
if ([version]$phpVersion -lt [version]"8.3.0") {
    throw "PHP 8.3+ required, found $phpVersion"
}

$nodeMajor = [int](node -p "process.versions.node.split('.')[0]")
if ($nodeMajor -lt 20) {
    throw "Node.js 20+ required"
}

Write-Host "==> Environment file"
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.production.example") {
        Copy-Item ".env.production.example" ".env"
    } else {
        Copy-Item ".env.example" ".env"
    }
    Write-Host "Created .env"
}

Write-Host "==> PHP dependencies"
composer install --no-interaction --prefer-dist

$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch 'APP_KEY=base64:') {
    php artisan key:generate --force
}

Write-Host "==> Database"
php artisan migrate --force
php artisan app:bootstrap --force

Write-Host "==> Frontend"
npm ci
npm run build

Write-Host "==> Health gate"
$appUrl = "http://localhost"
$match = Select-String -Path ".env" -Pattern '^APP_URL=' | Select-Object -First 1
if ($match) {
    $appUrl = ($match.Line -split '=', 2)[1].Trim('"')
}

$healthy = $false
for ($i = 1; $i -le 30; $i++) {
    try {
        $response = Invoke-WebRequest -Uri "$appUrl/health" -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Host "Health check OK: $appUrl/health"
            $healthy = $true
            break
        }
    } catch {
        Start-Sleep -Seconds 2
    }
}

if (-not $healthy) {
    Write-Host "Warning: /health did not respond. Start the web server and verify manually."
}

Write-Host "==> Install complete"
