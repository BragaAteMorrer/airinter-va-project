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

[Languages]
Name: "french"; MessagesFile: "compiler:Languages\French.isl"

[Files]
Source: "..\dist\Promethee-ACARS-win-x64-{#MyAppVersion}\{#MyAppExeName}"; DestDir: "{app}"; Flags: ignoreversion

[Tasks]
Name: "desktopicon"; Description: "Créer un raccourci sur le Bureau"; GroupDescription: "Raccourcis :"; Flags: unchecked

[Icons]
Name: "{autoprograms}\Air Inter\Hermès ACARS"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"
Name: "{autodesktop}\Hermès ACARS"; Filename: "{app}\{#MyAppExeName}"; WorkingDir: "{app}"; Tasks: desktopicon

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "Lancer Hermès ACARS"; Flags: nowait postinstall skipifsilent
