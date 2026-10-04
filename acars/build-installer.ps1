param(
  [ValidatePattern('^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$')]
  [string]$Version = '1.0.0',
  [string]$CertificatePath = $env:HERMES_SIGNING_CERTIFICATE,
  [string]$CertificatePassword = $env:HERMES_SIGNING_CERTIFICATE_PASSWORD,
  [switch]$IncludeMsfs2024Efb,
  [string]$Msfs2024SdkRoot = $(if ($env:MSFS2024_SDK) { $env:MSFS2024_SDK } else { 'C:\MSFS 2024 SDK' }),
  [string]$EfbDistPath
)
$ErrorActionPreference = 'Stop'

& (Join-Path $PSScriptRoot 'build-release.ps1') -Version $Version

if ($IncludeMsfs2024Efb) {
  $efbArgs = @{
    Version = $Version
    SdkRoot = $Msfs2024SdkRoot
  }
  if ($EfbDistPath) {
    $efbArgs.EfbDistPath = $EfbDistPath
    $efbArgs.SkipAppBuild = $true
  }
  & (Join-Path $PSScriptRoot 'msfs2024-efb\build-efb-package.ps1') @efbArgs
  if ($LASTEXITCODE -ne 0) { throw 'La création du package EFB MSFS 2024 a échoué.' }
}

$candidates = @(
  (Get-Command ISCC.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -ErrorAction SilentlyContinue),
  (Join-Path ${env:ProgramFiles(x86)} 'Inno Setup 6\ISCC.exe'),
  (Join-Path $env:ProgramFiles 'Inno Setup 6\ISCC.exe')
) | Where-Object { $_ -and (Test-Path -LiteralPath $_) }

$iscc = $candidates | Select-Object -First 1
if (-not $iscc) {
  throw "Inno Setup 6 est requis - installez-le avec Chocolatey (choco install innosetup)."
}

$isccArgs = @("/DMyAppVersion=$Version")
if ($IncludeMsfs2024Efb) { $isccArgs += '/DIncludeMsfs2024Efb=1' }
$isccArgs += (Join-Path $PSScriptRoot 'installer.iss')
& $iscc @isccArgs
if ($LASTEXITCODE -ne 0) { throw "La creation de l'installateur Hermes a echoue." }

$dist = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$releaseDir = Join-Path $dist "Promethee-ACARS-win-x64-$Version"
$clientExe = Join-Path $releaseDir 'Promethee.Acars.exe'
$portableZip = Join-Path $dist "Promethee-ACARS-win-x64-$Version.zip"
$setup = Join-Path $dist "Hermes-ACARS-Setup-$Version.exe"
if (-not (Test-Path -LiteralPath $setup)) { throw "Installateur introuvable: $setup" }


if ($CertificatePath) {
  if (-not (Test-Path -LiteralPath $CertificatePath)) { throw "Certificat de signature introuvable: $CertificatePath" }
  $signtool = Get-ChildItem "${env:ProgramFiles(x86)}\Windows Kits\10\bin" -Filter signtool.exe -Recurse -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -match '\\x64\\signtool\.exe$' } |
    Sort-Object FullName -Descending |
    Select-Object -First 1
  if (-not $signtool) { throw "signtool.exe introuvable. Installez le Windows SDK." }

  if (-not (Test-Path -LiteralPath $clientExe)) { throw "Executable Hermès introuvable: $clientExe" }

  $baseSignArgs = @('sign','/fd','SHA256','/tr','http://timestamp.digicert.com','/td','SHA256','/f',$CertificatePath)
  if ($CertificatePassword) { $baseSignArgs += @('/p',$CertificatePassword) }

  $pfxFlags = [System.Security.Cryptography.X509Certificates.X509KeyStorageFlags]::EphemeralKeySet
  $signingCertificate = if ($CertificatePassword) {
    [System.Security.Cryptography.X509Certificates.X509Certificate2]::new($CertificatePath, $CertificatePassword, $pfxFlags)
  } else {
    [System.Security.Cryptography.X509Certificates.X509Certificate2]::new($CertificatePath)
  }

  function Assert-HermesAuthenticodeSignature {
    param(
      [Parameter(Mandatory=$true)][string]$Path,
      [Parameter(Mandatory=$true)][string]$ExpectedThumbprint
    )

    $signature = Get-AuthenticodeSignature -LiteralPath $Path
    if (-not $signature.SignerCertificate) {
      throw "Signature Authenticode absente: $Path"
    }

    if (-not $signature.SignerCertificate.Thumbprint.Equals($ExpectedThumbprint, [StringComparison]::OrdinalIgnoreCase)) {
      throw "Le certificat Authenticode ne correspond pas au certificat de build: $Path"
    }

    # The public fallback is intentionally self-signed, so Windows may return
    # NotTrusted/UnknownError solely because its root is not installed. Those
    # trust-chain statuses are acceptable here; structural signature failures
    # (missing signature, altered hash, unsupported/incompatible signature)
    # are never accepted.
    $fatalStatuses = @('NotSigned','HashMismatch','NotSupported','Incompatible')
    if ($fatalStatuses -contains [string]$signature.Status) {
      throw "Signature Authenticode invalide ($($signature.Status)): $Path"
    }

    if (-not $signature.TimeStamperCertificate) {
      throw "Horodatage Authenticode absent: $Path"
    }

    Write-Host "Signature Authenticode vérifiée: $Path -> $($signature.Status) / $($signature.SignerCertificate.Subject)"
  }

  & $signtool.FullName @($baseSignArgs + $clientExe)
  if ($LASTEXITCODE -ne 0) { throw "La signature Authenticode du client Hermès a échoué." }

  Assert-HermesAuthenticodeSignature -Path $clientExe -ExpectedThumbprint $signingCertificate.Thumbprint

  if (Test-Path -LiteralPath $portableZip) { Remove-Item -LiteralPath $portableZip -Force }
  Compress-Archive -Path $clientExe -DestinationPath $portableZip -Force
  $zipHash = Get-FileHash -Algorithm SHA256 -LiteralPath $portableZip
  "$($zipHash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($portableZip))" |
    Set-Content -LiteralPath "$portableZip.sha256" -Encoding ascii

  & $signtool.FullName @($baseSignArgs + $setup)
  if ($LASTEXITCODE -ne 0) { throw "La signature Authenticode de l'installateur a échoué." }

  Assert-HermesAuthenticodeSignature -Path $setup -ExpectedThumbprint $signingCertificate.Thumbprint
  Write-Host "Signature Authenticode présente, intègre et horodatée."
} else {
  Write-Warning "Installateur NON SIGNE : configurez HERMES_SIGNING_CERTIFICATE pour une release publique."
}

$hash = Get-FileHash -Algorithm SHA256 -LiteralPath $setup
"$($hash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($setup))" |
  Set-Content -LiteralPath "$setup.sha256" -Encoding ascii
Write-Host "Installateur cree: $setup"
Write-Host "SHA-256: $($hash.Hash)"
if ($IncludeMsfs2024Efb) {
  $efbZip = Join-Path $dist "AirInter-Hermes-EFB-MSFS2024-$Version.zip"
  if (-not (Test-Path -LiteralPath $efbZip)) { throw "ZIP EFB introuvable: $efbZip" }
  Write-Host "EFB MSFS 2024 inclus dans le Setup : $efbZip"
}
