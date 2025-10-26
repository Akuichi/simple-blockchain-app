# Quick Start Guide

## For Windows Users (PowerShell)

### Option 1: Automated Setup (Recommended)

1. Open PowerShell in the project directory
2. Run the setup script:
```powershell
.\setup.bat
```

### Option 2: Manual Setup

1. **Create environment files:**
```powershell
Copy-Item backend\.env.example backend\.env
Copy-Item frontend\.env.example frontend\.env
```

2. **Start Docker containers:**
```powershell
docker-compose up --build -d
```

3. **Wait for containers to start (30 seconds), then setup database:**
```powershell
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

4. **Access the application:**
- Frontend: http://localhost:5173
- Backend: http://localhost:8000

## For Linux/Mac Users

### Option 1: Automated Setup (Recommended)

1. Make the setup script executable:
```bash
chmod +x setup.sh
```

2. Run the setup script:
```bash
./setup.sh
```

### Option 2: Manual Setup

1. **Create environment files:**
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

2. **Start Docker containers:**
```bash
docker-compose up --build -d
```

3. **Wait for containers to start (30 seconds), then setup database:**
```bash
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

4. **Access the application:**
- Frontend: http://localhost:5173
- Backend: http://localhost:8000

## First Time Use

1. **Open** http://localhost:5173 in your browser
2. **Create transactions:**
   - Go to "Transactions" page
   - Fill in sender, receiver, and amount
   - Click "Create Transaction"
   - Repeat 3-5 times
3. **Mine a block:**
   - Go to "Dashboard"
   - Click "Mine Block" button
   - Wait for mining to complete
4. **Validate blockchain:**
   - Click "Validate Chain" button
   - Should show green "Blockchain is Valid"
5. **View blockchain:**
   - Go to "Blockchain" page
   - See all mined blocks
   - Click any block to expand details

## Common Issues

### "Port already in use"
```powershell
# Stop any existing containers
docker-compose down

# Change ports in docker-compose.yml if needed
# Frontend: "5173:5173" -> "3000:5173"
# Backend: "8000:8000" -> "8080:8000"
```

### "Cannot connect to database"
```powershell
# Check if PostgreSQL is running
docker ps | Select-String postgres

# Restart the database
docker restart blockchain_postgres

# Wait 10 seconds and try again
```

### "Composer dependencies not installed"
```powershell
# Access backend container
docker exec -it blockchain_backend bash

# Install dependencies
composer install

# Exit container
exit
```

### "Yarn dependencies not installed"
```powershell
# Access frontend container
docker exec -it blockchain_frontend sh

# Install dependencies
yarn install

# Exit container
exit
```

## Useful Commands

### View Logs
```powershell
# All containers
docker-compose logs

# Specific container
docker-compose logs backend
docker-compose logs frontend
docker-compose logs postgres

# Follow logs (real-time)
docker-compose logs -f backend
```

### Reset Everything
```powershell
# Stop and remove everything
docker-compose down -v

# Rebuild from scratch
docker-compose up --build -d

# Re-run migrations
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

### Access Containers
```powershell
# Backend (Laravel)
docker exec -it blockchain_backend bash

# Frontend (React)
docker exec -it blockchain_frontend sh

# Database (PostgreSQL)
docker exec -it blockchain_postgres psql -U blockchain_user -d blockchain
```

## Development Workflow

### Making Backend Changes

1. Edit files in `backend/` directory
2. Changes are automatically reflected (volume mounted)
3. If you modify composer.json:
```powershell
docker exec blockchain_backend composer install
docker restart blockchain_backend
```

### Making Frontend Changes

1. Edit files in `frontend/src/` directory
2. Vite hot-reload automatically updates browser
3. If you modify package.json:
```powershell
docker exec blockchain_frontend yarn install
docker restart blockchain_frontend
```

### Database Changes

1. Create new migration:
```powershell
docker exec blockchain_backend php artisan make:migration create_something_table
```

2. Edit the migration file in `backend/database/migrations/`

3. Run migration:
```powershell
docker exec blockchain_backend php artisan migrate
```

## Stopping the Application

```powershell
# Stop containers (keeps data)
docker-compose stop

# Stop and remove containers (keeps volumes)
docker-compose down

# Stop and remove everything including data
docker-compose down -v
```

## Restarting the Application

```powershell
# If you used 'docker-compose stop'
docker-compose start

# If you used 'docker-compose down'
docker-compose up -d
```

## Testing API with cURL

### Create Transaction
```powershell
curl -X POST http://localhost:8000/api/v1/transaction `
  -H "Content-Type: application/json" `
  -d '{"sender":"Alice","receiver":"Bob","amount":100.50}'
```

### Get Pending Transactions
```powershell
curl http://localhost:8000/api/v1/transactions/pending
```

### Mine Block
```powershell
curl -X POST http://localhost:8000/api/v1/block/mine
```

### Validate Blockchain
```powershell
curl http://localhost:8000/api/v1/blockchain/validate
```

### Get All Blocks
```powershell
curl http://localhost:8000/api/v1/blocks
```

## Next Steps

1. Read `README.md` for complete documentation
2. Read `SECURITY.md` to understand security implementation
3. Experiment with the application
4. Take screenshots for your assignment
5. Write your one-page security explanation

## Support

If you encounter issues:
1. Check the logs: `docker-compose logs`
2. Verify all containers are running: `docker ps`
3. Ensure ports 5173, 8000, and 5432 are not in use
4. Try resetting: `docker-compose down -v` then rebuild
