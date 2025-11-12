@echo off
REM Script para criar o banco de dados plataforma_x
REM Certifique-se de que MySQL está instalado e no PATH

echo ===================================
echo Plataforma X - Instalação do Banco
echo ===================================
echo.

REM Verificar se MySQL está disponível
where mysql >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERRO] MySQL não encontrado no PATH
    echo Por favor, instale MySQL ou adicione-o ao PATH
    pause
    exit /b 1
)

REM Solicitar credenciais
set /p DB_USER="Digite o usuário MySQL (padrão: root): " || set DB_USER=root
set /p DB_PASS="Digite a senha MySQL (deixe em branco para sem senha): " || set DB_PASS=

REM Construir comando de conexão
if "%DB_PASS%"=="" (
    set DB_CMD=mysql -u %DB_USER%
) else (
    set DB_CMD=mysql -u %DB_USER% -p%DB_PASS%
)

echo.
echo [INFO] Criando banco de dados...
echo.

REM Executar script SQL
if exist "database\database.sql" (
    %DB_CMD% < database\database.sql
    if %errorlevel% equ 0 (
        echo.
        echo [SUCESSO] Banco de dados criado com sucesso!
        echo.
        echo Configurações:
        echo - Database: plataforma_x
        echo - User: usuario_plataforma
        echo - Password: senha_segura_123
        echo.
    ) else (
        echo [ERRO] Falha ao criar banco de dados
        pause
        exit /b 1
    )
) else (
    echo [ERRO] Arquivo database.sql não encontrado
    pause
    exit /b 1
)

REM Perguntar se deseja carregar dados de exemplo
echo.
set /p LOAD_EXAMPLE="Deseja carregar dados de exemplo? (s/n): "
if /i "%LOAD_EXAMPLE%"=="s" (
    if exist "database\dados_exemplo.sql" (
        %DB_CMD% plataforma_x < database\dados_exemplo.sql
        if %errorlevel% equ 0 (
            echo [SUCESSO] Dados de exemplo carregados!
        ) else (
            echo [ERRO] Falha ao carregar dados de exemplo
        )
    ) else (
        echo [ERRO] Arquivo dados_exemplo.sql não encontrado
    )
)

echo.
echo ===================================
echo Instalação Concluída!
echo ===================================
echo.
echo Próximos passos:
echo 1. Certifique-se de que os arquivos estão em: C:\xampp\htdocs\plataforma_x
echo 2. Inicie o Apache e MySQL no XAMPP
echo 3. Abra no navegador: http://localhost/plataforma_x/
echo.
pause
