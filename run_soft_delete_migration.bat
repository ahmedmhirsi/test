@echo off
echo ========================================
echo Migration : Ajout champ deleted_by_client
echo ========================================
echo.

REM Chemin vers MySQL
set MYSQL_PATH=c:\xampp\mysql\bin\mysql.exe
set DB_NAME=gestion_reclamations

echo Execution de la migration...
%MYSQL_PATH% -u root %DB_NAME% < migration_soft_delete.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] Migration executee avec succes !
    echo La colonne deleted_by_client a ete ajoutee.
) else (
    echo.
    echo [ERREUR] La migration a echoue.
    echo Verifiez que MySQL est demarre.
)

echo.
pause
