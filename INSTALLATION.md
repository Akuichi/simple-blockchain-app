# Blockchain Application - Installation Verification

## Pre-Installation Checklist

Before starting installation, verify you have:

- [ ] Docker Desktop installed and running
- [ ] At least 2GB free disk space
- [ ] Ports 5173, 8000, and 5432 available
- [ ] Internet connection for downloading dependencies

## Installation Steps Tracking

### Step 1: Environment Setup
- [ ] Cloned/Downloaded project
- [ ] Opened terminal in project directory
- [ ] Created `backend/.env` file
- [ ] Created `frontend/.env` file

### Step 2: Docker Container Setup
- [ ] Ran `docker-compose up --build -d`
- [ ] Confirmed PostgreSQL container started
- [ ] Confirmed Backend container started
- [ ] Confirmed Frontend container started

### Step 3: Database Setup
- [ ] Ran migrations: `php artisan migrate`
- [ ] Seeded database: `php artisan db:seed`
- [ ] Verified genesis block created

### Step 4: Verification
- [ ] Frontend loads at http://localhost:5173
- [ ] Backend responds at http://localhost:8000
- [ ] Can create transactions
- [ ] Can mine blocks
- [ ] Can validate blockchain

## Build Tracking (For README)

This section tracks how the application was built for easy reproduction:

### Backend Structure Created
1. ✅ Laravel 12 project structure
2. ✅ Database configuration (PostgreSQL)
3. ✅ Migration files (transactions, blocks, block_transactions)
4. ✅ Models (Transaction, Block) with relationships
5. ✅ BlockchainService (hashing, mining, validation)
6. ✅ Controllers (TransactionController, BlockController, BlockchainController)
7. ✅ API routes
8. ✅ CORS configuration
9. ✅ Database seeder (genesis block)

### Frontend Structure Created
1. ✅ React 18 + Vite project
2. ✅ TailwindCSS configuration
3. ✅ React Router setup
4. ✅ API service (axios)
5. ✅ Layout component
6. ✅ Dashboard page
7. ✅ Transactions page
8. ✅ Blocks page

### Docker Configuration
1. ✅ docker-compose.yml (3 services)
2. ✅ Backend Dockerfile (PHP 8.3)
3. ✅ Frontend Dockerfile (Node 20)
4. ✅ PostgreSQL service configuration
5. ✅ Networking setup
6. ✅ Volume management

### Documentation Created
1. ✅ README.md (comprehensive guide)
2. ✅ SECURITY.md (security explanation)
3. ✅ QUICKSTART.md (quick setup guide)
4. ✅ This file (INSTALLATION.md)
5. ✅ Setup scripts (setup.sh, setup.bat)

## Dependencies Installed

### Backend (Laravel)
```json
{
  "php": "^8.2",
  "laravel/framework": "^12.0",
  "laravel/tinker": "^2.10"
}
```

### Frontend (React - Yarn)
```json
{
  "react": "^18.3.1",
  "react-dom": "^18.3.1",
  "react-router-dom": "^6.26.0",
  "axios": "^1.7.2",
  "vite": "^5.3.4",
  "tailwindcss": "^3.4.7"
}
```

### Infrastructure
- Docker Engine 20.10+
- Docker Compose 2.0+
- PostgreSQL 16 (Alpine)
- Node.js 20 (Alpine)
- PHP 8.3 (FPM)

## Installation on Fresh Machine

To install this application on a new machine, follow these steps IN ORDER:

### 1. Install Prerequisites
```powershell
# Install Docker Desktop from:
# https://www.docker.com/products/docker-desktop

# Verify installation
docker --version
docker-compose --version
```

### 2. Clone Project
```powershell
git clone <repository-url>
cd blockchain
```

### 3. Run Setup Script
```powershell
# Windows
.\setup.bat

# Linux/Mac
chmod +x setup.sh
./setup.sh
```

### 4. Verify Installation
```powershell
# Check containers are running
docker ps

# Should see:
# - blockchain_postgres
# - blockchain_backend
# - blockchain_frontend

# Check frontend
# Open browser to http://localhost:5173

# Check backend
curl http://localhost:8000/api/v1/blockchain/stats
```

## Manual Installation (Alternative)

If automated script fails, follow manual steps:

### 1. Environment Files
```powershell
# Windows
Copy-Item backend\.env.example backend\.env
Copy-Item frontend\.env.example frontend\.env

# Linux/Mac
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### 2. Start Docker Services
```powershell
docker-compose up --build -d
```

### 3. Wait for Services
```powershell
# Wait 30 seconds for database to initialize
Start-Sleep -Seconds 30  # PowerShell
# OR
sleep 30  # Bash
```

### 4. Initialize Database
```powershell
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

### 5. Verify
```powershell
# Check logs
docker-compose logs

# Access application
Start-Process http://localhost:5173  # PowerShell
# OR
open http://localhost:5173  # Mac
# OR
xdg-open http://localhost:5173  # Linux
```

## Troubleshooting Installation

### Issue: "Composer install failed"
```powershell
docker exec -it blockchain_backend bash
composer install --no-interaction
exit
docker restart blockchain_backend
```

### Issue: "Yarn install failed"
```powershell
docker exec -it blockchain_frontend sh
yarn install
exit
docker restart blockchain_frontend
```

### Issue: "Port 5432 already in use"
```powershell
# Stop existing PostgreSQL
# Windows: Stop service in Services app
# Linux: sudo systemctl stop postgresql

# OR change port in docker-compose.yml
# Change "5432:5432" to "5433:5432"
# Update backend/.env: DB_PORT=5433
```

### Issue: "Migration failed"
```powershell
docker exec blockchain_backend php artisan migrate:fresh --force
docker exec blockchain_backend php artisan db:seed --force
```

### Issue: "Cannot connect to backend"
```powershell
# Check if backend is running
docker logs blockchain_backend

# Restart backend
docker restart blockchain_backend

# Check CORS settings in backend/config/cors.php
```

## Post-Installation Testing

After installation, test all functionality:

### Test 1: Create Transaction
1. Go to http://localhost:5173/transactions
2. Fill form: Alice -> Bob, $100
3. Click "Create Transaction"
4. Should see success message
5. Transaction appears in pending list

### Test 2: Mine Block
1. Go to http://localhost:5173
2. Click "Mine Block"
3. Wait for completion (few seconds)
4. Should see success message
5. Pending count decreases to 0

### Test 3: Validate Blockchain
1. Stay on Dashboard
2. Click "Validate Chain"
3. Should show green "Blockchain is Valid"
4. Stats should update

### Test 4: View Blockchain
1. Go to http://localhost:5173/blocks
2. Should see Genesis Block (purple)
3. Should see your mined block
4. Click block to expand
5. Should see transaction details

### Test 5: API Direct Test
```powershell
# Test backend directly
curl http://localhost:8000/api/v1/blocks

# Should return JSON with blocks
```

## Installation Complete Checklist

- [ ] All containers running
- [ ] Database initialized
- [ ] Genesis block created
- [ ] Frontend accessible
- [ ] Backend API responding
- [ ] Can create transactions
- [ ] Can mine blocks
- [ ] Can validate blockchain
- [ ] Can view blocks
- [ ] No errors in logs

## Next Steps After Installation

1. ✅ **Read Documentation**
   - README.md for full guide
   - SECURITY.md for security details
   - QUICKSTART.md for quick reference

2. ✅ **Experiment with Application**
   - Create multiple transactions
   - Mine several blocks
   - Test validation
   - Try to detect tampering

3. ✅ **Prepare Assignment Submission**
   - Take screenshots
   - Write security explanation
   - Document your understanding
   - Test presentation demo

## Uninstallation

To remove the application:

```powershell
# Stop and remove containers, networks, volumes
docker-compose down -v

# Remove images (optional)
docker rmi blockchain_backend blockchain_frontend

# Delete project folder
# Windows: Remove-Item -Recurse -Force blockchain
# Linux/Mac: rm -rf blockchain
```

## Re-installation

To reinstall after uninstallation:

```powershell
# Start from Step 2 of "Installation on Fresh Machine"
docker-compose up --build -d
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

---

**Installation Date**: _________________  
**Installed By**: _________________  
**Installation Success**: ☐ Yes ☐ No  
**Notes**: _________________________________
