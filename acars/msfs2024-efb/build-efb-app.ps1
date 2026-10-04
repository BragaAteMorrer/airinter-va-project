param(
  [string]$SdkRoot = $(if ($env:MSFS2024_SDK) { $env:MSFS2024_SDK } else { 'C:\MSFS 2024 SDK' }),
  [switch]$Clean
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$appRoot = Join-Path $root 'HermesEfb'
$localApi = Join-Path $root 'efb_api'
$sdkApi = Join-Path $SdkRoot 'Samples\DevmodeProjects\EFB\PackageSources\efb_api'

if (-not (Test-Path -LiteralPath $sdkApi)) {
  throw "API EFB MSFS 2024 introuvable sous '$sdkApi'. Installez le SDK MSFS 2024 ou passez -SdkRoot."
}
if (-not (Get-Command npm.cmd -ErrorAction SilentlyContinue)) {
  throw 'Node.js / npm est requis pour compiler l’EFB Hermès.'
}

if ($Clean -and (Test-Path -LiteralPath $localApi)) {
  Remove-Item -LiteralPath $localApi -Recurse -Force
}
if (-not (Test-Path -LiteralPath $localApi)) {
  Copy-Item -LiteralPath $sdkApi -Destination $localApi -Recurse -Force
}

Push-Location $localApi
try {
  & npm.cmd install --no-audit --no-fund
  if ($LASTEXITCODE -ne 0) { throw "npm install efb_api a échoué ($LASTEXITCODE)." }
}
finally { Pop-Location }

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
Write-Host "EFB Hermès compilé : $dist"
