param(
  [ValidatePattern('^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$')]
  [string]$Version = '1.0.0'
)
$ErrorActionPreference = 'Stop'

& (Join-Path $PSScriptRoot 'build-release.ps1') -Version $Version

$candidates = @(
  (Get-Command ISCC.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -ErrorAction SilentlyContinue),
  (Join-Path ${env:ProgramFiles(x86)} 'Inno Setup 6\ISCC.exe'),
  (Join-Path $env:ProgramFiles 'Inno Setup 6\ISCC.exe')
) | Where-Object { $_ -and (Test-Path -LiteralPath $_) }

$iscc = $candidates | Select-Object -First 1
if (-not $iscc) {
  throw "Inno Setup 6 est requis - installez-le avec Chocolatey (choco install innosetup)."
}

& $iscc "/DMyAppVersion=$Version" (Join-Path $PSScriptRoot 'installer.iss')
if ($LASTEXITCODE -ne 0) { throw "La creation de l'installateur Hermes a echoue." }

$dist = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$setup = Join-Path $dist "Hermes-ACARS-Setup-$Version.exe"
if (-not (Test-Path -LiteralPath $setup)) { throw "Installateur introuvable: $setup" }

$hash = Get-FileHash -Algorithm SHA256 -LiteralPath $setup
"$($hash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($setup))" |
  Set-Content -LiteralPath "$setup.sha256" -Encoding ascii
Write-Host "Installateur cree: $setup"
Write-Host "SHA-256: $($hash.Hash)"
