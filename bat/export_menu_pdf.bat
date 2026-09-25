@echo off
REM Genera un PDF del menu pubblico usando Chrome headless.
REM Uso: export_menu_pdf.bat [URL] [nome_file_output.pdf]

setlocal

set "URL=%~1"
if "%URL%"=="" set "URL=https://bartest.it/menu.php"

set "OUTPUT=%~2"
if "%OUTPUT%"=="" set "OUTPUT=menu_export.pdf"

set "CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist "%CHROME%" set "CHROME=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"

if not exist "%CHROME%" (
    echo Errore: non trovo chrome.exe nei percorsi standard.
    exit /b 1
)

"%CHROME%" --headless=new --print-to-pdf="%OUTPUT%" --no-pdf-header-footer --run-all-compositor-stages-before-draw --virtual-time-budget=10000 "%URL%"

if exist "%OUTPUT%" (
    echo PDF generato: %OUTPUT%
) else (
    echo Errore: il PDF non e' stato generato.
    exit /b 1
)

endlocal
