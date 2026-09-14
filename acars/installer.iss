; Requires Inno Setup 6. Build after build-release.ps1, passing /DMyAppVersion=1.0.0.
#define MyAppName "Promethee ACARS"
#ifndef MyAppVersion
  #define MyAppVersion "dev"
#endif
#define MyAppExeName "Promethee.Acars.exe"

[Setup]
AppId={{A39EE8D5-A1CF-4BB8-866E-6A963A7E69A5}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
DefaultDirName={localappdata}\AirInter\Promethee ACARS
DefaultGroupName=Air Inter
OutputDir=..\dist
OutputBaseFilename=Promethee-ACARS-Setup-{#MyAppVersion}
Compression=lzma2
SolidCompression=yes
PrivilegesRequired=lowest

[Files]
Source: "..\dist\Promethee-ACARS-win-x64-{#MyAppVersion}\{#MyAppExeName}"; DestDir: "{app}"; Flags: ignoreversion

[Icons]
Name: "{autoprograms}\Air Inter\Promethee ACARS"; Filename: "{app}\{#MyAppExeName}"
Name: "{autodesktop}\Promethee ACARS"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon

[Tasks]
Name: "desktopicon"; Description: "Créer un raccourci sur le Bureau"; Flags: unchecked

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "Lancer Promethee ACARS"; Flags: nowait postinstall skipifsilent
