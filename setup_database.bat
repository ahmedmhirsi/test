@echo off
echo ===================================================
echo    Configuration de la base de donnees
echo ===================================================
echo.
echo IMPORTANT: Assurez-vous que MySQL dans XAMPP est demarre !
echo.
pause

echo.
echo 1. Creation de la base de donnees...
c:\xampp\php\php.exe bin/console doctrine:database:create --if-not-exists

echo.
echo 2. Generation des migrations...
c:\xampp\php\php.exe bin/console doctrine:migrations:diff

echo.
echo 3. Application des migrations...
c:\xampp\php\php.exe bin/console doctrine:migrations:migrate --no-interaction

echo.
echo 4. Verification du schema...
c:\xampp\php\php.exe bin/console doctrine:schema:validate

echo.
echo ===================================================
echo    Configuration terminee !
echo ===================================================
pause
