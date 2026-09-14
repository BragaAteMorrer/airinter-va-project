param([string]$Version = 'dev')
$ErrorActionPreference = 'Stop'
& (Join-Path $PSScriptRoot 'build-release.ps1') -Version $Version
$iscc = Get-Command ISCC.exe -ErrorAction SilentlyContinue
if (-not $iscc) { throw 'Inno Setup 6 est requis pour créer le setup : https://jrsoftware.org/isdl.php' }
& $iscc.Source "/DMyAppVersion=$Version" (Join-Path $PSScriptRoot 'installer.iss')
if ($LASTEXITCODE -ne 0) { throw 'La création de l’installateur a échoué.' }
