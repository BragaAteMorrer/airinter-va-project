#define MyAppName "Hermès"
#ifndef MyAppVersion
#define MyAppVersion "0.1.0"
#endif
#ifndef PublishDir
#define PublishDir "..\artifacts\hermes"
#endif
[Setup]
AppId={{8D3F5E20-4F6E-4A31-A719-44E7B6381A74}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher=Air Inter Virtual Airlines
DefaultDirName={autopf}\Air Inter VA\Hermes
DefaultGroupName=Air Inter VA
OutputDir=..\artifacts\installer
OutputBaseFilename=Hermes-Setup-{#MyAppVersion}-win-x64
Compression=lzma2
SolidCompression=yes
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
PrivilegesRequired=lowest
WizardStyle=modern
UninstallDisplayIcon={app}\Promethee.Acars.exe
[Files]
Source: "{#PublishDir}\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs
[Icons]
Name: "{autoprograms}\Air Inter VA\Hermès"; Filename: "{app}\Promethee.Acars.exe"
Name: "{autodesktop}\Hermès"; Filename: "{app}\Promethee.Acars.exe"; Tasks: desktopicon
[Tasks]
Name: "desktopicon"; Description: "Créer un raccourci sur le Bureau"; GroupDescription: "Raccourcis :"
[Run]
Filename: "{app}\Promethee.Acars.exe"; Description: "Lancer Hermès"; Flags: nowait postinstall skipifsilent
