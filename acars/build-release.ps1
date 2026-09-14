param(
    [string]$Version = 'dev'
)

$ErrorActionPreference = 'Stop'

$project = Join-Path $PSScriptRoot 'Promethee.Acars.csproj'
# Keep the output outside the project directory. Otherwise the Web SDK sees an
# earlier release as static content and embeds that release into the next EXE.
$releaseRoot = Join-Path (Split-Path $PSScriptRoot -Parent) 'dist'
$release = Join-Path $releaseRoot "Promethee-ACARS-win-x64-$Version"

dotnet publish $project -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -p:IncludeAllContentForSelfExtract=true -p:DebugType=None -p:DebugSymbols=false -o $release
if ($LASTEXITCODE -ne 0) { throw 'La publication de Prométhée ACARS a échoué.' }

Compress-Archive -Path (Join-Path $release 'Promethee.Acars.exe') -DestinationPath "$release.zip" -Force
Write-Host "Distribution créée : $release.zip"
Write-Host 'À distribuer : le ZIP. Le pilote décompresse puis lance Promethee.Acars.exe.'
