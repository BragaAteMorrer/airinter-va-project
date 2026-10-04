param(
  [string]$PackageSource,
  [string]$InstalledPackagesPath,
  [string]$StateFile,
  [switch]$Uninstall
)

$ErrorActionPreference = 'Stop'
$packageName = 'airinter-hermes-efb'

function Get-ConfiguredPackagesPath {
  param([string]$ExplicitPath)

  if (-not [string]::IsNullOrWhiteSpace($ExplicitPath)) {
    return [IO.Path]::GetFullPath($ExplicitPath)
  }

  $candidates = @(
    (Join-Path $env:LOCALAPPDATA 'Packages\Microsoft.Limitless_8wekyb3d8bbwe\LocalCache\UserCfg.opt'),
    (Join-Path $env:APPDATA 'Microsoft Flight Simulator 2024\UserCfg.opt'),
    (Join-Path $env:LOCALAPPDATA 'Microsoft Flight Simulator 2024\UserCfg.opt')
  ) | Select-Object -Unique

  foreach ($cfg in $candidates) {
    if (-not (Test-Path -LiteralPath $cfg)) { continue }
    foreach ($line in Get-Content -LiteralPath $cfg -ErrorAction Stop) {
      if ($line -match '^\s*InstalledPackagesPath\s+"(.+)"\s*$') {
        return [IO.Path]::GetFullPath($Matches[1])
      }
    }
  }

  return $null
}

function Assert-SafePackageTarget {
  param([string]$CommunityRoot, [string]$Target)

  $community = [IO.Path]::GetFullPath($CommunityRoot).TrimEnd('\')
  $targetFull = [IO.Path]::GetFullPath($Target)
  $requiredPrefix = $community + '\'
  if (-not $targetFull.StartsWith($requiredPrefix, [StringComparison]::OrdinalIgnoreCase)) {
    throw "Refus de modifier un chemin hors Community2024 : $targetFull"
  }
  if ([IO.Path]::GetFileName($targetFull) -ne $packageName) {
    throw "Nom de package inattendu : $targetFull"
  }
}

if ($Uninstall) {
  $target = $null
  if ($StateFile -and (Test-Path -LiteralPath $StateFile)) {
    $target = (Get-Content -LiteralPath $StateFile -Raw).Trim()
  }

  if ([string]::IsNullOrWhiteSpace($target)) {
    $packages = Get-ConfiguredPackagesPath -ExplicitPath $InstalledPackagesPath
    if ($packages) {
      $target = Join-Path (Join-Path $packages 'Community2024') $packageName
    }
  }

  if ($target) {
    $community = Split-Path $target -Parent
    Assert-SafePackageTarget -CommunityRoot $community -Target $target
    if (Test-Path -LiteralPath $target) {
      Remove-Item -LiteralPath $target -Recurse -Force
      Write-Host "EFB Hermès désinstallé : $target"
    }
  }
  if ($StateFile -and (Test-Path -LiteralPath $StateFile)) {
    Remove-Item -LiteralPath $StateFile -Force
  }
  exit 0
}

if ([string]::IsNullOrWhiteSpace($PackageSource)) {
  throw 'PackageSource est requis pour l’installation.'
}
$source = [IO.Path]::GetFullPath($PackageSource)
if (-not (Test-Path -LiteralPath (Join-Path $source 'manifest.json'))) {
  throw "Package EFB invalide : manifest.json absent de '$source'."
}
if (-not (Test-Path -LiteralPath (Join-Path $source 'layout.json'))) {
  throw "Package EFB invalide : layout.json absent de '$source'."
}

$packagesRoot = Get-ConfiguredPackagesPath -ExplicitPath $InstalledPackagesPath
if ([string]::IsNullOrWhiteSpace($packagesRoot)) {
  throw 'MSFS 2024 UserCfg.opt introuvable. Impossible de déterminer Community2024.'
}
if (-not (Test-Path -LiteralPath $packagesRoot)) {
  throw "InstalledPackagesPath MSFS 2024 introuvable : '$packagesRoot'."
}

$community = Join-Path $packagesRoot 'Community2024'
New-Item -ItemType Directory -Path $community -Force | Out-Null
$target = Join-Path $community $packageName
Assert-SafePackageTarget -CommunityRoot $community -Target $target

if (Test-Path -LiteralPath $target) {
  Remove-Item -LiteralPath $target -Recurse -Force
}
New-Item -ItemType Directory -Path $target -Force | Out-Null
Copy-Item -Path (Join-Path $source '*') -Destination $target -Recurse -Force

if ($StateFile) {
  $stateParent = Split-Path $StateFile -Parent
  if ($stateParent) { New-Item -ItemType Directory -Path $stateParent -Force | Out-Null }
  [IO.File]::WriteAllText($StateFile, $target, (New-Object System.Text.UTF8Encoding($false)))
}

Write-Host "EFB Hermès installé : $target"
Write-Host 'Redémarrez MSFS 2024 pour charger ou mettre à jour l’application EFB.'
