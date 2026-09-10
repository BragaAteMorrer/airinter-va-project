$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
docker compose -f compose.promethee.yml up --build -d
if ($LASTEXITCODE -ne 0) { throw 'Le démarrage Docker a échoué. Vérifier Docker Desktop et WSL.' }
Write-Host 'Promethee : http://localhost:8088/promethee'
Write-Host 'Promethee ACARS : http://127.0.0.1:1974'
Write-Host 'Compte local : admin@promethee.test / promethee-local'
Write-Host 'Cle API locale : voir docker compose -f compose.promethee.yml logs web'
