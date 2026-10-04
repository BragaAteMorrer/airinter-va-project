param(
  [ValidatePattern('^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?$')]
  [string]$Version = '1.0.0',
  [string]$EfbDistPath,
  [switch]$SkipAppBuild
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$repoRoot = Split-Path $projectRoot -Parent
$outputRoot = Join-Path $repoRoot "dist\AirInter-Hermes-EFB-MSFS2024-$Version"
$packageName = 'airinter-hermes-efb'
$packageRoot = Join-Path $outputRoot $packageName
$appTarget = Join-Path $packageRoot 'html_ui\efb_ui\efb_apps\AirInterHermes'
$zipPath = Join-Path $repoRoot "dist\AirInter-Hermes-EFB-MSFS2024-$Version.zip"

if (-not $SkipAppBuild) {
  & (Join-Path $PSScriptRoot 'build-efb-app.ps1')
  if ($LASTEXITCODE -ne 0) { throw 'Compilation EFB échouée.' }
}

if ([string]::IsNullOrWhiteSpace($EfbDistPath)) {
  $EfbDistPath = Join-Path $PSScriptRoot 'HermesEfb\dist'
}
$EfbDistPath = [IO.Path]::GetFullPath($EfbDistPath)
if (-not (Test-Path -LiteralPath $EfbDistPath)) {
  throw "Dist EFB introuvable : $EfbDistPath"
}
if (-not (Test-Path -LiteralPath (Join-Path $EfbDistPath 'HermesEfb.js'))) {
  throw "Dist EFB invalide : HermesEfb.js est absent de '$EfbDistPath'."
}

if (Test-Path -LiteralPath $outputRoot) { Remove-Item -LiteralPath $outputRoot -Recurse -Force }
if (Test-Path -LiteralPath $zipPath) { Remove-Item -LiteralPath $zipPath -Force }
New-Item -ItemType Directory -Path $appTarget -Force | Out-Null
Copy-Item -Path (Join-Path $EfbDistPath '*') -Destination $appTarget -Recurse -Force

$contentFiles = @(Get-ChildItem -LiteralPath $packageRoot -Recurse -File |
  Where-Object { $_.Name -notin @('layout.json','manifest.json') } |
  Sort-Object FullName)

$layoutContent = foreach ($file in $contentFiles) {
  $relative = $file.FullName.Substring($packageRoot.Length + 1).Replace('\','/').ToLowerInvariant()
  [ordered]@{
    path = $relative
    size = [int64]$file.Length
    date = [int64]$file.LastWriteTimeUtc.ToFileTimeUtc()
  }
}
$totalSize = [int64](($contentFiles | Measure-Object -Property Length -Sum).Sum)
$packageVersion = (($Version -split '[-+]')[0])

$manifest = [ordered]@{
  dependencies = @()
  content_type = 'MISC'
  title = 'Air Inter Hermès EFB'
  manufacturer = 'Air Inter VA'
  creator = 'Air Inter VA'
  package_version = $packageVersion
  minimum_game_version = '1.7.0'
  minimum_compatibility_version = '1.0.0.0'
  export_type = 'Community'
  builder = 'Air Inter Hermès'
  package_order_hint = 'MISC'
  release_notes = [ordered]@{
    neutral = [ordered]@{
      LastUpdate = "Hermès EFB $Version"
      OlderHistory = ''
    }
  }
  total_package_size = $totalSize.ToString().PadLeft(20, '0')
}

$utf8 = New-Object System.Text.UTF8Encoding($false)
$layoutJson = ([ordered]@{ content = @($layoutContent) } | ConvertTo-Json -Depth 8)
$manifestJson = ($manifest | ConvertTo-Json -Depth 8)
[IO.File]::WriteAllText((Join-Path $packageRoot 'layout.json'), $layoutJson, $utf8)
[IO.File]::WriteAllText((Join-Path $packageRoot 'manifest.json'), $manifestJson, $utf8)

Compress-Archive -Path $packageRoot -DestinationPath $zipPath -Force
$hash = Get-FileHash -Algorithm SHA256 -LiteralPath $zipPath
"$($hash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($zipPath))" |
  Set-Content -LiteralPath "$zipPath.sha256" -Encoding ascii

Write-Host "Package Community2024 : $packageRoot"
Write-Host "ZIP public : $zipPath"
Write-Host "SHA-256 : $($hash.Hash)"
