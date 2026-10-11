param([string]$Version = 'dev')
$ErrorActionPreference = 'Stop'
$project = Join-Path $PSScriptRoot 'Promethee.Acars.csproj'
$releaseRoot = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$release = Join-Path $releaseRoot "Promethee-ACARS-win-x64-$Version"
$zip = "$release.zip"

# A named release is replaced from its source; never merge into an old wwwroot.
if (Test-Path -LiteralPath $release) { Remove-Item -LiteralPath $release -Recurse -Force }
if (Test-Path -LiteralPath $zip) { Remove-Item -LiteralPath $zip -Force }
New-Item -ItemType Directory -Path $release -Force | Out-Null
# .NET AssemblyVersion/FileVersion require numeric components; SemVer prerelease
# labels remain in Version and InformationalVersion for package identity.
$numericVersion = if ($Version -match '^(\\d+)\\.(\\d+)\\.(\\d+)') {
  "$($Matches[1]).$($Matches[2]).$($Matches[3]).0"
} else {
  '0.0.0.0'
}
dotnet publish $project -f net8.0-windows -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:IncludeAllContentForSelfExtract=true -p:DebugType=None -p:DebugSymbols=false -p:Version=$Version -p:AssemblyVersion=$numericVersion -p:FileVersion=$numericVersion -p:InformationalVersion=$Version -o $release
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath (Join-Path $release 'Promethee.Acars.exe'))) {
  throw 'Build ACARS échoué — distribution non créée.'
}
Compress-Archive -Path (Join-Path $release 'Promethee.Acars.exe') -DestinationPath $zip -Force
$zipHash = Get-FileHash -Algorithm SHA256 -LiteralPath $zip
"$($zipHash.Hash.ToLowerInvariant())  $([IO.Path]::GetFileName($zip))" | Set-Content -LiteralPath "$zip.sha256" -Encoding ascii
Write-Host "Distribution créée : $zip"
