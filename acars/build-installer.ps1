param(
  [ValidatePattern('^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$')]
  [string]$Version = '1.0.0',
  [string]$CertificatePath = $env:HERMES_SIGNING_CERTIFICATE,
  [string]$CertificatePassword = $env:HERMES_SIGNING_CERTIFICATE_PASSWORD
)
$ErrorActionPreference = 'Stop'

& (Join-Path $PSScriptRoot 'build-release.ps1') -Version $Version

$dist = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$releaseDir = Join-Path $dist "Promethee-ACARS-win-x64-$Version"
$clientExe = Join-Path $releaseDir 'Promethee.Acars.exe'
$portableZip = Join-Path $dist "Promethee-ACARS-win-x64-$Version.zip"
$setup = Join-Path $dist "Hermes-ACARS-Setup-$Version.exe"

$candidates = @(
  (Get-Command ISCC.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -ErrorAction SilentlyContinue),
  (Join-Path ${env:ProgramFiles(x86)} 'Inno Setup 6\ISCC.exe'),
  (Join-Path $env:ProgramFiles 'Inno Setup 6\ISCC.exe')
) | Where-Object { $_ -and (Test-Path -LiteralPath $_) }

$iscc = $candidates | Select-Object -First 1
if (-not $iscc) {
  throw "Inno Setup 6 est requis - installez-le avec Chocolatey (choco install innosetup)."
}

$signtool = $null
$baseSignArgs = $null

if ($CertificatePath) {
  if (-not (Test-Path -LiteralPath $CertificatePath)) {
    throw "Certificat de signature introuvable: $CertificatePath"
  }

  $signtool = Get-ChildItem "${env:ProgramFiles(x86)}\Windows Kits\10\bin" -Filter signtool.exe -Recurse -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -match '\\x64\\signtool\.exe$' } |
    Sort-Object FullName -Descending |
    Select-Object -First 1

  if (-not $signtool) {
    throw "signtool.exe introuvable. Installez le Windows SDK."
  }

  if (-not (Test-Path -LiteralPath $clientExe)) {
    throw "Executable Hermès introuvable: $clientExe"
  }

  $baseSignArgs = @('sign','/fd','SHA256','/tr','http://timestamp.digicert.com','/td','SHA256','/f',$CertificatePath)
  if ($CertificatePassword) {
    $baseSignArgs += @('/p',$CertificatePassword)
  }

  & $signtool.FullName @($baseSignArgs + $clientExe)
  if ($LASTEXITCODE -ne 0) {
    throw "La signature Authenticode du client Hermès a échoué."
  }

  $clientSignature = Get-AuthenticodeSignature -FilePath $clientExe
  if (-not $clientSignature.SignerCertificate) {
    throw "La signature Authenticode du client Hermès est absente."
  }

  Write-Host "Client Hermès signé par: $($clientSignature.SignerCertificate.Subject) [$($clientSignature.Status)]"

  if (Test-Path -LiteralPath $portableZip) {
    Remove-Item -LiteralPath $portableZip -Force
  }

  Compress-Archive -Path $clientExe -DestinationPath $portableZip -Force

  $zipHash = Get-FileHash -Algorithm SHA256 -LiteralPath $portableZip
  "$($zipHash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($portableZip))" |
    Set-Content -LiteralPath "$portableZip.sha256" -Encoding ascii
}

& $iscc "/DMyAppVersion=$Version" (Join-Path $PSScriptRoot 'installer.iss')
if ($LASTEXITCODE -ne 0) {
  throw "La creation de l'installateur Hermes a echoue."
}

if (-not (Test-Path -LiteralPath $setup)) {
  throw "Installateur introuvable: $setup"
}

if ($CertificatePath) {
  & $signtool.FullName @($baseSignArgs + $setup)
  if ($LASTEXITCODE -ne 0) {
    throw "La signature Authenticode de l'installateur a échoué."
  }

  $setupSignature = Get-AuthenticodeSignature -FilePath $setup
  if (-not $setupSignature.SignerCertificate) {
    throw "La signature Authenticode de l'installateur est absente."
  }

  Write-Host "Installateur signé par: $($setupSignature.SignerCertificate.Subject) [$($setupSignature.Status)]"
  Write-Host "Signature Authenticode présente (certificat auto-signé temporaire)."
} else {
  Write-Warning "Installateur NON SIGNE : configurez HERMES_SIGNING_CERTIFICATE pour une release publique."
}

$hash = Get-FileHash -Algorithm SHA256 -LiteralPath $setup
"$($hash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($setup))" |
  Set-Content -LiteralPath "$setup.sha256" -Encoding ascii

Write-Host "Installateur cree: $setup"
Write-Host "SHA-256: $($hash.Hash)"
