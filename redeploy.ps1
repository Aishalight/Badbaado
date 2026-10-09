<#
    Redeploy BADBAADO on Alwaysdata.

    This does NOT push your code: commit and push locally first, then run this.
    It opens an SSH session that pulls the latest code on the server and runs
    scripts/deploy.sh (maintenance mode, DB snapshot, migrate, build, optimize).

    Usage (PowerShell, from anywhere):
        .\redeploy.ps1                      full redeploy
        .\redeploy.ps1 --no-build           keep the existing frontend assets
        .\redeploy.ps1 --no-pull --no-migrate
#>

$ErrorActionPreference = "Stop"
$Remote   = "badbaado@ssh-badbaado.alwaysdata.net"
$AppPath  = "~/www/badbaado"
$RepoRoot = if ($PSScriptRoot) { $PSScriptRoot } else { (Get-Location).Path }
$DeployArgs = @($args)

Write-Host "==> BADBAADO redeploy" -ForegroundColor Cyan

# Warn (do not block) if the local checkout has work that is not on GitHub yet.
if (Get-Command git -ErrorAction SilentlyContinue) {
    $status   = & git -C $RepoRoot status --porcelain 2>$null
    $tracking = & git -C $RepoRoot status -sb 2>$null

    if ($status) {
        Write-Host "WARN  uncommitted local changes are NOT included in this deploy." -ForegroundColor Yellow
    }
    if ($tracking -match 'ahead') {
        $branch = & git -C $RepoRoot rev-parse --abbrev-ref HEAD 2>$null
        Write-Host "WARN  branch '$branch' has unpushed commits; push before the server can pull them." -ForegroundColor Yellow
    }
}

$argLine   = ($DeployArgs -join ' ').Trim()
$remoteCmd = "cd $AppPath && bash scripts/deploy.sh $argLine".Trim()

Write-Host "--> ssh $Remote" -ForegroundColor DarkGray
& ssh $Remote $remoteCmd
if ($LASTEXITCODE -ne 0) {
    Write-Host "Redeploy FAILED (exit $LASTEXITCODE)." -ForegroundColor Red
    exit $LASTEXITCODE
}

Write-Host "==> Done: https://badbaado.alwaysdata.net" -ForegroundColor Green
