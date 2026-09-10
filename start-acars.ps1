$ErrorActionPreference = 'Stop'

$project = Join-Path $PSScriptRoot 'acars\Promethee.Acars.csproj'
$output = Join-Path $env:USERPROFILE 'Promethee-acars'

dotnet publish $project -c Release -r win-x64 --self-contained false -o $output
if ($LASTEXITCODE -ne 0) { throw 'La publication de Prométhée ACARS a échoué. Vérifier .NET 8 SDK.' }

Write-Host 'Promethee ACARS : http://127.0.0.1:1974'
Write-Host 'Laisser cette fenêtre ouverte pendant le vol.'
& (Join-Path $output 'Promethee.Acars.exe')
