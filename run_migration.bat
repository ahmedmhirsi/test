@echo off
echo ================================================
echo   Migration : Ajout de la colonne auteur_type
echo ================================================
echo.

cd /d "%~dp0"

echo Execution de la migration SQL...
c:\xampp\mysql\bin\mysql.exe -u root gestion_reclamations < migration_auteur_type.sql

if errorlevel 1 (
    echo.
    echo ERREUR: La migration a echoue
    echo.
    pause
    exit /b 1
)

echo.
echo ================================================
echo   SUCCES ! La migration est terminee
echo ================================================
echo.
echo La colonne auteur_type a ete ajoutee a la table reponse.
echo Vous pouvez maintenant utiliser le systeme de conversation.
echo.
pause
