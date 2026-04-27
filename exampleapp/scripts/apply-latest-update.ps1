param(
    [string]$Branch = "master"
)

$ErrorActionPreference = "Stop"
$env:GIT_TERMINAL_PROMPT = "0"
$repoRoot = Split-Path -Parent $PSScriptRoot

function Assert-Command {
    param(
        [string]$Name
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Required command not found on PATH: $Name"
    }
}

function Invoke-Step {
    param(
        [string]$FilePath,
        [string[]]$Arguments = @()
    )

    Write-Host "> $FilePath $($Arguments -join ' ')" -ForegroundColor DarkGray
    & $FilePath @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: $FilePath $($Arguments -join ' ')"
    }
}

Push-Location $repoRoot

try {
    Assert-Command "git"

    git rev-parse --is-inside-work-tree | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: git rev-parse --is-inside-work-tree"
    }

    $appPrefix = git rev-parse --show-prefix
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: git rev-parse --show-prefix"
    }

    $appPathspec = "."
    if (-not $appPrefix) {
        $appPathspec = "."
    } else {
        $appPathspec = ":/$appPrefix"
    }

    $status = git status --short -- $appPathspec

    if ($status) {
        Write-Host "Uncommitted changes found inside exampleapp:" -ForegroundColor Yellow
        $status | ForEach-Object { Write-Host "  $_" }
        throw "Commit/stash your current changes first, then run update script again."
    }

    Write-Host "Fetching latest changes..." -ForegroundColor Cyan
    Invoke-Step "git" @("fetch", "origin", "--tags")

    git show-ref --verify --quiet "refs/remotes/origin/$Branch"
    if ($LASTEXITCODE -ne 0) {
        throw "Remote branch not found: origin/$Branch. Check APP_UPDATE_BRANCH in .env."
    }

    $currentBranch = git branch --show-current
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: git branch --show-current"
    }

    if ($currentBranch -ne $Branch) {
        Invoke-Step "git" @("checkout", $Branch)
    }

    Invoke-Step "git" @("pull", "--ff-only", "origin", $Branch)

    if (Test-Path "composer.json") {
        Assert-Command "composer"
        Write-Host "Installing PHP dependencies..." -ForegroundColor Cyan
        Invoke-Step "composer" @("install", "--no-interaction", "--prefer-dist")
    }

    if (Test-Path "package.json") {
        Assert-Command "npm"
        Write-Host "Installing Node dependencies and building assets..." -ForegroundColor Cyan
        Invoke-Step "npm" @("install")
        Invoke-Step "npm" @("run", "build")
    }

    Assert-Command "php"
    Write-Host "Running Laravel update tasks..." -ForegroundColor Cyan
    Invoke-Step "php" @("artisan", "migrate", "--force")
    Invoke-Step "php" @("artisan", "optimize:clear")

    Write-Host "Update complete. Current version:" -ForegroundColor Green
    Get-Content "VERSION"
}
catch {
    [Console]::Error.WriteLine("ERROR: $($_.Exception.Message)")
    exit 1
}
finally {
    Pop-Location
}
