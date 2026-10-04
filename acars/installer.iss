; Hermès Windows installer. Build with build-installer.ps1 -Version 1.0.0.
#define MyAppName "Hermès ACARS"
#define MyAppPublisher "Air Inter VA"
#define MyAppURL "https://airinter-va.org"
#ifndef MyAppVersion
  #define MyAppVersion "dev"
#endif
#define MyAppExeName "Promethee.Acars.exe"

[Setup]
AppId={{A39EE8D5-A1CF-4BB8-866E-6A963A7E69A5}
AppName={#MyAppName}
AppVerName={#MyAppName} {#MyAppVersion}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={localappdata}\AirInter\Hermes
DefaultGroupName=Air Inter
DisableProgramGroupPage=yes
OutputDir=..\dist
OutputBaseFilename=Hermes-ACARS-Setup-{#MyAppVersion}
Compression=lzma2/ultra64
SolidCompression=yes
PrivilegesRequired=lowest
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
MinVersion=10.0.17763
CloseApplications=yes
RestartApplications=no
UninstallDisplayName={#MyAppName}
UninstallDisplayIcon={app}\{#MyAppExeName}
VersionInfoVersion={#MyAppVersion}
VersionInfoCompany={#MyAppPublisher}
VersionInfoDescription=Client ACARS officiel de Prométhée
VersionInfoProductName={#MyAppName}
VersionInfoProductVersion={#MyAppVersion}
WizardStyle=modern
SetupLogging=yes
SetupIconFile=assets\hermes.ico

[Languages]
Name: "french"; MessagesFile: "compiler:Languages\French.isl"

[Files]
Source: "..\dist\Promethee-ACARS-win-x64-{#MyAppVersion}\{#MyAppExeName}"; DestDir: "{app}"; Flags: ignoreversion
#ifdef IncludeMsfs2024Efb
Source: "msfs2024-efb\install-efb-package.ps1"; DestDir: "{app}\tools"; Flags: ignoreversion
Source: "..\dist\AirInter-Hermes-EFB-MSFS2024-{#MyAppVersion}\airinter-hermes-efb\*"; DestDir: "{tmp}\AirInterHermesEfb\airinter-hermes-efb"; Flags: ignoreversion recursesubdirs createallsubdirs; Tasks: msfs2024efb
#endif

[Tasks]
Name: "desktopicon"; Description: "Créer un raccourci sur le Bureau"; GroupDescription: "Raccourcis :"; Flags: unchecked
#ifdef IncludeMsfs2024Efb
Name: "msfs2024efb"; Description: "Installer l’application EFB Hermès dans Microsoft Flight Simulator 2024 (recommandé)"; GroupDescription: "Microsoft Flight Simulator 2024 :"; Check: IsMsfs2024Detected
#endif

[Icons]
Name: "{autoprograms}\Air Inter\Hermès ACARS"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"
Name: "{autodesktop}\Hermès ACARS"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"; Tasks: desktopicon

[Run]
#ifdef IncludeMsfs2024Efb
Filename: "{sys}\WindowsPowerShell\v1.0\powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\tools\install-efb-package.ps1"" -PackageSource ""{tmp}\AirInterHermesEfb\airinter-hermes-efb"" -StateFile ""{app}\efb-install.txt"""; StatusMsg: "Installation de l’EFB Hermès dans MSFS 2024…"; Flags: runhidden waituntilterminated; Tasks: msfs2024efb
#endif
Filename: "{app}\{#MyAppExeName}"; Description: "Lancer Hermès ACARS"; Flags: nowait postinstall skipifsilent

#ifdef IncludeMsfs2024Efb
[UninstallRun]
Filename: "{sys}\WindowsPowerShell\v1.0\powershell.exe"; Parameters: "-NoProfile -ExecutionPolicy Bypass -File ""{app}\tools\install-efb-package.ps1"" -Uninstall -StateFile ""{app}\efb-install.txt"""; Flags: runhidden waituntilterminated
#endif

[Code]
function IsMsfs2024Detected: Boolean;
begin
  Result :=
    FileExists(ExpandConstant('{localappdata}\Packages\Microsoft.Limitless_8wekyb3d8bbwe\LocalCache\UserCfg.opt')) or
    FileExists(ExpandConstant('{userappdata}\Microsoft Flight Simulator 2024\UserCfg.opt')) or
    FileExists(ExpandConstant('{localappdata}\Microsoft Flight Simulator 2024\UserCfg.opt'));
end;
