@echo off
setlocal EnableExtensions EnableDelayedExpansion

set "BRANCH=master"

:parse_args
if "%~1"=="" goto args_done
if /I "%~1"=="-Branch" (
    set "BRANCH=%~2"
    shift
    shift
    goto parse_args
)
set "BRANCH=%~1"
shift
goto parse_args

:args_done
set "SCRIPT_DIR=%~dp0"
set "APP_ROOT="
if exist "%SCRIPT_DIR%composer.json" (
    for %%I in ("%SCRIPT_DIR%.") do set "APP_ROOT=%%~fI"
) else (
    for %%I in ("%SCRIPT_DIR%..") do set "APP_ROOT=%%~fI"
)

if "%ACADCLEAR_UPDATE_DEBUG%"=="1" (
    echo DEBUG: SCRIPT_DIR=%SCRIPT_DIR%
    echo DEBUG: APP_ROOT=%APP_ROOT%
)

pushd "%APP_ROOT%" || (
    echo ERROR: Cannot access app root: "%APP_ROOT%"
    exit /b 1
)

if "%ACADCLEAR_UPDATE_DEBUG%"=="1" echo DEBUG: CWD=%CD%

git rev-parse --is-inside-work-tree >nul
if errorlevel 1 (
    echo ERROR: Command failed with exit code %ERRORLEVEL%: git rev-parse --is-inside-work-tree
    goto fail
)

set "GIT_DIR="
for /f "delims=" %%A in ('git rev-parse --git-dir') do set "GIT_DIR=%%A"
if errorlevel 1 (
    echo ERROR: Command failed with exit code %ERRORLEVEL%: git rev-parse --git-dir
    goto fail
)

set "GIT_WRITE_TEST=%GIT_DIR%\acadclear-update-write-test.tmp"
break > "%GIT_WRITE_TEST%" 2>nul
if errorlevel 1 (
    echo ERROR: The updater cannot write to "%GIT_DIR%". Give the web server user write permission to this repository's .git folder, then try again.
    goto fail
)
del "%GIT_WRITE_TEST%" >nul 2>nul

echo Fetching latest changes...
call :run git fetch origin --tags
if errorlevel 1 goto fail

git show-ref --verify --quiet "refs/remotes/origin/%BRANCH%"
if errorlevel 1 (
    echo ERROR: Remote branch not found: origin/%BRANCH%. Check APP_UPDATE_BRANCH in .env.
    goto fail
)

set "CURRENT_BRANCH="
for /f "delims=" %%A in ('git branch --show-current') do set "CURRENT_BRANCH=%%A"
if errorlevel 1 goto fail

if /I not "%CURRENT_BRANCH%"=="%BRANCH%" (
    call :run git checkout "%BRANCH%"
    if errorlevel 1 goto fail
)

call :run git pull --ff-only origin "%BRANCH%"
if errorlevel 1 goto fail

if exist composer.json (
    echo Installing PHP dependencies...
    call :run composer install --no-interaction --prefer-dist
    if errorlevel 1 goto fail
)

if exist package.json (
    echo Installing Node dependencies and building assets...
    call :run npm install
    if errorlevel 1 goto fail

    call :run npm run build
    if errorlevel 1 goto fail
)

echo Running Laravel update tasks...
call :run php artisan migrate --force
if errorlevel 1 goto fail

call :run php artisan optimize:clear
if errorlevel 1 goto fail

echo Update complete. Current version:
type VERSION

popd
exit /b 0

:run
set "COMMAND=%*"
%*
set "EXIT_CODE=%ERRORLEVEL%"
if not "%EXIT_CODE%"=="0" (
    echo ERROR: Command failed with exit code %EXIT_CODE%: %COMMAND%
    exit /b %EXIT_CODE%
)
exit /b 0

:fail
set "EXIT_CODE=%ERRORLEVEL%"
if "%EXIT_CODE%"=="0" set "EXIT_CODE=1"
popd
exit /b %EXIT_CODE%
