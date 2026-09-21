@echo off
setlocal
rem CMD cannot use a UNC path as its current directory. pushd maps it to a
rem temporary drive and also works unchanged from a normal Windows checkout.
pushd "%~dp0" >nul 2>&1
if errorlevel 1 (
  echo Unable to access the Hermès source directory.
  exit /b 1
)

rem PowerShell can block unsigned scripts before their first instruction runs.
rem The bypass applies only to this child process; it does not alter Windows policy.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\start-acars.ps1"
set "exitCode=%ERRORLEVEL%"
popd

if not "%exitCode%"=="0" (
  echo.
  echo Build ACARS failed - previous version was not started.
  exit /b %exitCode%
)
