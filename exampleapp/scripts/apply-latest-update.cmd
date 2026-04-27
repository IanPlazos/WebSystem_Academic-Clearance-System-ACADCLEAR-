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

pushd "%APP_ROOT%" || exit /b 1

git rev-parse --is-inside-work-tree >nul
if errorlevel 1 (
    echo Command failed with exit code %ERRORLEVEL%: git rev-parse --is-inside-work-tree
    goto fail
)

set "APP_PREFIX="
for /f "delims=" %%A in ('git rev-parse --show-prefix') do set "APP_PREFIX=%%A"
set "APP_PATHSPEC=."
if defined APP_PREFIX set "APP_PATHSPEC=:/%APP_PREFIX%"

set "HAS_CHANGES="
for /f "delims=" %%A in ('git status --short -- "!APP_PATHSPEC!"') do (
    if not defined HAS_CHANGES (
        echo Uncommitted changes found inside exampleapp:
        set "HAS_CHANGES=1"
    )
    echo   %%A
)

if defined HAS_CHANGES (
    echo Commit/stash your current changes first, then run update script again.
    goto fail
)

echo Fetching latest changes...
call :run git fetch origin
if errorlevel 1 goto fail

set "CURRENT_BRANCH="
for /f "delims=" %%A in ('git branch --show-current') do set "CURRENT_BRANCH=%%A"
if errorlevel 1 goto fail

if /I not "%CURRENT_BRANCH%"=="%BRANCH%" (
    call :run git checkout "%BRANCH%"
    if errorlevel 1 goto fail
)

call :run git pull origin "%BRANCH%"
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
    echo Command failed with exit code %EXIT_CODE%: %COMMAND%
    exit /b %EXIT_CODE%
)
exit /b 0

:fail
set "EXIT_CODE=%ERRORLEVEL%"
if "%EXIT_CODE%"=="0" set "EXIT_CODE=1"
popd
exit /b %EXIT_CODE%
