@echo off
setlocal
rem PowerShell can block unsigned scripts before their first instruction runs.
rem The bypass applies only to this child process; it does not alter Windows policy.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-acars.ps1"
set "exitCode=%ERRORLEVEL%"
if not "%exitCode%"=="0" (
  echo.
  echo Build ACARS echoue - ancienne version non lancee.
  exit /b %exitCode%
)
