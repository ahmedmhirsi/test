@echo off
echo ===================================================
echo    Correction automatique des dependances Symfony
echo ===================================================
echo.

cd /d "%~dp0"

echo [1/5] Telechargement de Composer...
c:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
if errorlevel 1 (
    echo ERREUR: Impossible de telecharger Composer
    pause
    exit /b 1
)

echo [2/5] Installation de Composer...
c:\xampp\php\php.exe composer-setup.php
if errorlevel 1 (
    echo ERREUR: Installation de Composer echouee
    pause
    exit /b 1
)

echo [3/5] Nettoyage...
c:\xampp\php\php.exe -r "unlink('composer-setup.php');"

echo [4/5] Installation des bonnes dependances...
c:\xampp\php\php.exe composer.phar install --no-interaction
if errorlevel 1 (
    echo ERREUR: Installation des dependances echouee
    pause
    exit /b 1
)

echo [5/5] Nettoyage du cache Symfony...
c:\xampp\php\php.exe bin/console cache:clear

echo.
echo ===================================================
echo    TERMINE !
echo ===================================================
echo.
echo La validation PHP fonctionne maintenant correctement.
echo Vous pouvez redemarrer le serveur avec:
echo   c:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
echo.
pause
