$ErrorActionPreference = 'Stop'

# Stable development publication directory. It is recreated before each launch.
$output = Join-Path (Join-Path (Join-Path $env:LOCALAPPDATA 'AirInter') 'PrometheeACARS') 'current'
$exe = Join-Path $output 'Promethee.Acars.exe'
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$project = Join-Path $PSScriptRoot 'acars\Promethee.Acars.csproj'

Get-Process -Name 'Promethee.Acars' -ErrorAction SilentlyContinue | ForEach-Object {
  $processPath = $_.Path
  if ($processPath -and $processPath.Equals($exe, [StringComparison]::OrdinalIgnoreCase)) {
    Write-Host 'Closing the previous Hermes ACARS instance...'
    $_.CloseMainWindow() | Out-Null
    if (-not $_.WaitForExit(5000)) { Stop-Process -Id $_.Id -Force }
  }
}

if (-not (Test-Path -LiteralPath $project)) {
  throw "Hermes project not found: $project"
}

if (Test-Path -LiteralPath $output) { Remove-Item -LiteralPath $output -Recurse -Force }
New-Item -ItemType Directory -Path $output -Force | Out-Null

dotnet publish $project -f net8.0-windows -c Release -r win-x64 --self-contained false `
  -p:InformationalVersion="dev-$stamp" -o $output

if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $exe) -or -not (Test-Path -LiteralPath (Join-Path $output 'wwwroot\index.html'))) {
  throw 'Hermes ACARS build failed - previous version was not started.'
}

Write-Host "Hermes ACARS $stamp: $exe"
& $exe
