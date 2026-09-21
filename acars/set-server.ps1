param([Parameter(Mandatory = $true)][string]$Server)
$ErrorActionPreference = 'Stop'
try { $validatedServer = [uri]$Server } catch { throw 'URL invalide.' }
if ($validatedServer.Scheme -ne 'https' -or $validatedServer.UserInfo -or $validatedServer.Query -or $validatedServer.Fragment) { throw 'L’URL du serveur doit être HTTPS et sans paramètres.' }
Write-Host 'Le registre Windows n’est plus utilisé par Prométhée ACARS.'
Write-Host ('Pour cette session PowerShell : définir PROMETHEE_ACARS_SERVER sur ' + $validatedServer.AbsoluteUri.TrimEnd('/'))
