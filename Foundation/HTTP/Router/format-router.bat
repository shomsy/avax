@echo off
REM === PhpStorm CLI Formatter for Router Component ===
echo 🧽 Running PhpStorm formatter on Router component...

REM 🔧 PUT YOUR PhpStorm PATH HERE:
set PHPS_PATH="C:\Users\%USERNAME%\AppData\Local\JetBrains\Toolbox\apps\PhpStorm\ch-0\232.11272.36\bin\phpstorm64.exe"

REM 🗂️ PATH TO YOUR PROJECT
set PROJECT_PATH="C:\Users\%USERNAME%\PhpstormProjects\components\Foundation\HTTP\Router"

REM 💅 FORMAT USING PROJECT CODE STYLE
%PHPS_PATH% format -allowDefaults -r -s "%PROJECT_PATH%\.idea\codeStyles\Project.xml" %PROJECT_PATH%

echo ✅ PhpStorm Formatter finished successfully!
pause
