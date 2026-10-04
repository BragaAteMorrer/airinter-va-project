param(
  [ValidatePattern('^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$')]
  [string]$Version,
  [string]$SdkRoot = $(if ($env:MSFS2024_SDK) { $env:MSFS2024_SDK } else { 'C:\MSFS 2024 SDK' }),
  [string]$CertificatePath = $env:HERMES_SIGNING_CERTIFICATE,
  [string]$CertificatePassword = $env:HERMES_SIGNING_CERTIFICATE_PASSWORD,
  [switch]$Publish
)

$ErrorActionPreference = 'Stop'
if ([string]::IsNullOrWhiteSpace($Version)) { throw '-Version est requis.' }

$installerArgs = @{
  Version = $Version
  IncludeMsfs2024Efb = $true
  Msfs2024SdkRoot = $SdkRoot
}
if ($CertificatePath) { $installerArgs.CertificatePath = $CertificatePath }
if ($CertificatePassword) { $installerArgs.CertificatePassword = $CertificatePassword }

& (Join-Path $PSScriptRoot 'build-installer.ps1') @installerArgs

$dist = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$files = @(
  (Join-Path $dist "Hermes-ACARS-Setup-$Version.exe"),
  (Join-Path $dist "Hermes-ACARS-Setup-$Version.exe.sha256"),
  (Join-Path $dist "Promethee-ACARS-win-x64-$Version.zip"),
  (Join-Path $dist "Promethee-ACARS-win-x64-$Version.zip.sha256"),
  (Join-Path $dist "AirInter-Hermes-EFB-MSFS2024-$Version.zip"),
  (Join-Path $dist "AirInter-Hermes-EFB-MSFS2024-$Version.zip.sha256")
)

foreach ($file in $files) {
  if (-not (Test-Path -LiteralPath $file)) { throw "Artefact de release manquant : $file" }
}

Write-Host ''
Write-Host "Release publique Hermès $Version prête :"
$files | ForEach-Object { Write-Host " - $_" }

if (-not $Publish) { exit 0 }

$gh = Get-Command gh.exe -ErrorAction SilentlyContinue
if (-not $gh) { throw 'GitHub CLI (gh.exe) est requis avec -Publish.' }

& $gh.Source auth status
if ($LASTEXITCODE -ne 0) { throw 'GitHub CLI n’est pas authentifié.' }

$tag = "hermes-v$Version"
& $gh.Source release view $tag *> $null
$exists = $LASTEXITCODE -eq 0

if (-not $exists) {
  & $gh.Source release create $tag --title "Hermès $Version" --generate-notes @files
  if ($LASTEXITCODE -ne 0) { throw 'Création de la GitHub Release échouée.' }
} else {
  & $gh.Source release upload $tag --clobber @files
  if ($LASTEXITCODE -ne 0) { throw 'Upload des artefacts GitHub Release échoué.' }
}

Write-Host "GitHub Release publiée : $tag"
