param([Parameter(Mandatory = $true)][string]$Server)
$ErrorActionPreference = 'Stop'
try { $validatedServer = [uri]$Server } catch { throw 'URL invalide.' }
if ($validatedServer.Scheme -ne 'https') { throw 'L''URL du serveur doit utiliser HTTPS.' }
$key = 'HKLM:\SOFTWARE\AirInter\PrometheeACARS'
New-Item -Path $key -Force | Out-Null
Set-ItemProperty -Path $key -Name Server -Value $validatedServer.AbsoluteUri.TrimEnd('/')
Write-Host "Serveur ACARS verrouillé : $($validatedServer.AbsoluteUri.TrimEnd('/'))"
