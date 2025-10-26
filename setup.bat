@echo off
echo ================================
echo Blockchain Application Setup
echo ================================
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo X Docker is not installed. Please install Docker Desktop first.
    echo   Download from: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo √ Docker is installed

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo X Docker Compose is not installed.
    pause
    exit /b 1
)

echo √ Docker Compose is installed
echo.

REM Copy environment files if they don't exist
if not exist backend\.env (
    echo Creating backend .env file...
    copy backend\.env.example backend\.env >nul
    echo √ Backend .env created
) else (
    echo √ Backend .env already exists
)

if not exist frontend\.env (
    echo Creating frontend .env file...
    copy frontend\.env.example frontend\.env >nul
    echo √ Frontend .env created
) else (
    echo √ Frontend .env already exists
)

echo.
echo Building and starting Docker containers...
echo This may take 5-10 minutes on first run...
echo.

REM Build and start containers
docker-compose up --build -d

echo.
echo Waiting for database to be ready...
timeout /t 10 /nobreak >nul

echo.
echo Running database migrations...
docker exec blockchain_backend php artisan migrate --force

echo.
echo Seeding database (creating genesis block)...
docker exec blockchain_backend php artisan db:seed --force

echo.
echo ================================
echo Setup Complete!
echo ================================
echo.
echo Application URLs:
echo   Frontend: http://localhost:5173
echo   Backend:  http://localhost:8000
echo   Database: localhost:5432
echo.
echo Next steps:
echo   1. Open http://localhost:5173 in your browser
echo   2. Create some transactions
echo   3. Mine a block
echo   4. Validate the blockchain
echo.
echo To stop: docker-compose stop
echo To remove: docker-compose down
echo.
pause
