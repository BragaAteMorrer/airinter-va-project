param(
  [switch]$Clean
)

$ErrorActionPreference = 'Stop'
$appRoot = Join-Path $PSScriptRoot 'HermesEfb'

if (-not (Get-Command npm.cmd -ErrorAction SilentlyContinue)) {
  throw 'Node.js / npm est requis pour compiler l’EFB Hermès.'
}

if ($Clean) {
  foreach ($path in @(
    (Join-Path $appRoot 'node_modules'),
    (Join-Path $appRoot 'dist')
  )) {
    if (Test-Path -LiteralPath $path) { Remove-Item -LiteralPath $path -Recurse -Force }
  }
}

Push-Location $appRoot
try {
  & npm.cmd install --no-audit --no-fund
  if ($LASTEXITCODE -ne 0) { throw "npm install Hermès EFB a échoué ($LASTEXITCODE)." }

  & npm.cmd run typecheck
  if ($LASTEXITCODE -ne 0) { throw "Typecheck Hermès EFB a échoué ($LASTEXITCODE)." }

  & npm.cmd run build
  if ($LASTEXITCODE -ne 0) { throw "Build Hermès EFB a échoué ($LASTEXITCODE)." }
}
finally { Pop-Location }

$dist = Join-Path $appRoot 'dist'
if (-not (Test-Path -LiteralPath (Join-Path $dist 'HermesEfb.js'))) {
  throw "Build EFB incomplet : HermesEfb.js est introuvable dans '$dist'."
}
Write-Host "EFB Hermès compilé avec l’API npm Microsoft : $dist"
