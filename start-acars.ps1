$ErrorActionPreference = 'Stop'

# Stable development publication directory. It is recreated before each launch.
$output = Join-Path (Join-Path (Join-Path $env:LOCALAPPDATA 'AirInter') 'PrometheeACARS') 'current'
$exe = Join-Path $output 'Promethee.Acars.exe'
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'

Get-Process -Name 'Promethee.Acars' -ErrorAction SilentlyContinue | ForEach-Object {
  $processPath = $_.Path
  if ($processPath -and $processPath.Equals($exe, [StringComparison]::OrdinalIgnoreCase)) {
    Write-Host 'Fermeture de l''instance ACARS précédente...'
    $_.CloseMainWindow() | Out-Null
    if (-not $_.WaitForExit(5000)) { Stop-Process -Id $_.Id -Force }
  }
}

if (Test-Path -LiteralPath $output) { Remove-Item -LiteralPath $output -Recurse -Force }
New-Item -ItemType Directory -Path $output -Force | Out-Null

# cmd pushd maps an UNC source checkout to a temporary drive for dotnet.
if ($PSScriptRoot.StartsWith('\\')) {
  $command = 'pushd "' + $PSScriptRoot + '" && dotnet publish acars\Promethee.Acars.csproj -f net8.0-windows -c Release -r win-x64 --self-contained false -p:InformationalVersion=dev-' + $stamp + ' -o "' + $output + '" && popd'
  cmd.exe /d /s /c $command
} else {
  $project = Join-Path $PSScriptRoot 'acars\Promethee.Acars.csproj'
  dotnet publish $project -f net8.0-windows -c Release -r win-x64 --self-contained false -p:InformationalVersion="dev-$stamp" -o $output
}

if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $exe) -or -not (Test-Path -LiteralPath (Join-Path $output 'wwwroot\index.html'))) {
  throw 'Build ACARS échoué — ancienne version non lancée.'
}

Write-Host "Prométhée ACARS $stamp : $exe"
& $exe
