# 🚀 Complete Setup and Installation Guide

## Welcome!

This blockchain application is ready to install and run. Follow this guide step-by-step to get everything working.

---

## ⚡ Quick Installation (5 Minutes)

### For Windows Users:

1. **Open PowerShell** in the `blockchain` folder
2. **Run**: `.\setup.bat`
3. **Wait** for installation to complete
4. **Open browser** to http://localhost:5173
5. **Done!** Start using the app

### For Linux/Mac Users:

1. **Open Terminal** in the `blockchain` folder
2. **Run**: `chmod +x setup.sh && ./setup.sh`
3. **Wait** for installation to complete
4. **Open browser** to http://localhost:5173
5. **Done!** Start using the app

---

## 📋 Detailed Installation Steps

### Step 1: Prerequisites

**Install Docker Desktop:**
- Windows: https://docs.docker.com/desktop/install/windows-install/
- Mac: https://docs.docker.com/desktop/install/mac-install/
- Linux: https://docs.docker.com/desktop/install/linux-install/

**Verify Docker Installation:**
```bash
docker --version
docker-compose --version
```

Should show Docker version 20.10+ and Docker Compose version 2.0+

### Step 2: Prepare Environment Files

**Windows (PowerShell):**
```powershell
Copy-Item backend\.env.example backend\.env
Copy-Item frontend\.env.example frontend\.env
```

**Linux/Mac (Bash):**
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### Step 3: Start Docker Containers

```bash
docker-compose up --build -d
```

This will:
- Download required images (~2-3 GB)
- Build Laravel backend container
- Build React frontend container
- Start PostgreSQL database
- Install all dependencies

**Expected time**: 5-10 minutes on first run

### Step 4: Initialize Database

Wait 30 seconds for containers to fully start, then:

```bash
# Run database migrations
docker exec blockchain_backend php artisan migrate --force

# Create genesis block (first block in blockchain)
docker exec blockchain_backend php artisan db:seed --force
```

### Step 5: Verify Installation

**Check containers are running:**
```bash
docker ps
```

Should see 3 containers:
- `blockchain_postgres` (database)
- `blockchain_backend` (Laravel API)
- `blockchain_frontend` (React UI)

**Test frontend:**
- Open browser to http://localhost:5173
- Should see "Blockchain App" interface

**Test backend:**
- Open browser to http://localhost:8000
- Should see: `{"message":"Blockchain API is running"}`

---

## 🎮 First Use Tutorial

### 1. Create Your First Transaction

1. Go to http://localhost:5173
2. Click **"Transactions"** in navigation
3. Fill in the form:
   - **Sender**: Alice
   - **Receiver**: Bob
   - **Amount**: 100.00
4. Click **"Create Transaction"**
5. ✅ Success! Transaction is now pending

**Repeat** this 3-5 times with different values to have multiple transactions

### 2. Mine Your First Block

1. Go to **"Dashboard"** page
2. You should see pending transactions count > 0
3. Click **"Mine Block"** button
4. ⏳ Wait a few seconds (proof of work in progress)
5. ✅ Success! Block has been mined

**What happened?**
- System found a valid nonce (proof of work)
- Created a new block with all pending transactions
- Added block to the blockchain
- All transactions marked as "mined"

### 3. Validate the Blockchain

1. Stay on **"Dashboard"** page
2. Click **"Validate Chain"** button
3. ✅ Should show green: "Blockchain is Valid and Secure"

**What was checked?**
- Each block's hash is correct
- Previous hash links are valid
- Proof of work meets difficulty
- No tampering detected

### 4. View the Blockchain

1. Click **"Blockchain"** in navigation
2. See the complete chain:
   - **Block #0** (Genesis Block) - purple highlight
   - **Block #1** (Your mined block)
3. Click on a block to expand details
4. View all transactions in that block

---

## 🔍 Testing Tampering Detection

Want to see how the blockchain detects tampering?

### 1. Access Database

```bash
docker exec -it blockchain_postgres psql -U blockchain_user -d blockchain
```

### 2. View Current Blocks

```sql
SELECT id, index_no, current_hash FROM blocks;
```

### 3. Tamper with a Block

```sql
UPDATE blocks SET current_hash = 'tampered_hash_12345' WHERE index_no = 1;
```

### 4. Exit Database

```sql
\q
```

### 5. Validate Blockchain Again

1. Go to Dashboard
2. Click "Validate Chain"
3. ❌ Should show red: "Blockchain is Invalid"
4. See error messages explaining what's wrong

### 6. Fix the Blockchain

```bash
# Reset database
docker exec blockchain_backend php artisan migrate:fresh --force
docker exec blockchain_backend php artisan db:seed --force
```

Now validation should pass again!

---

## 📚 Important Files & Documentation

### Must Read:
- **README.md** - Complete documentation (read first!)
- **SECURITY.md** - Security implementation explanation
- **QUICKSTART.md** - Quick reference guide

### Reference:
- **INSTALLATION.md** - Detailed installation tracking
- **PROJECT_SUMMARY.md** - Project overview and deliverables

---

## 🎯 API Testing (Optional)

You can also interact with the backend directly:

### Create Transaction via API

```bash
curl -X POST http://localhost:8000/api/v1/transaction \
  -H "Content-Type: application/json" \
  -d '{"sender":"Charlie","receiver":"Diana","amount":75.50}'
```

### Get Pending Transactions

```bash
curl http://localhost:8000/api/v1/transactions/pending
```

### Mine Block via API

```bash
curl -X POST http://localhost:8000/api/v1/block/mine
```

### Get All Blocks

```bash
curl http://localhost:8000/api/v1/blocks
```

### Validate Blockchain

```bash
curl http://localhost:8000/api/v1/blockchain/validate
```

---

## 🛠️ Common Issues & Solutions

### Issue: "Port already in use"

**Solution:**
```bash
# Check what's using the port
netstat -ano | findstr :5173    # Windows
lsof -i :5173                   # Mac/Linux

# Stop conflicting service or change ports in docker-compose.yml
```

### Issue: "Cannot connect to database"

**Solution:**
```bash
# Restart database container
docker restart blockchain_postgres

# Wait 10 seconds
# Try migrations again
docker exec blockchain_backend php artisan migrate --force
```

### Issue: "Containers won't start"

**Solution:**
```bash
# Complete reset
docker-compose down -v
docker-compose up --build -d

# Wait 30 seconds
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

### Issue: "Frontend shows blank page"

**Solution:**
```bash
# Check frontend logs
docker logs blockchain_frontend

# Restart frontend
docker restart blockchain_frontend

# Access container and reinstall
docker exec -it blockchain_frontend sh
yarn install
exit
docker restart blockchain_frontend
```

### Issue: "Backend returns 500 error"

**Solution:**
```bash
# Check backend logs
docker logs blockchain_backend

# Access container
docker exec -it blockchain_backend bash

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check database connection
php artisan migrate:status

exit
```

---

## 🎓 Understanding the Code

### Backend Structure (Laravel)

```
backend/app/
├── Models/
│   ├── Transaction.php     - Transaction database model
│   └── Block.php           - Block database model
├── Services/
│   └── BlockchainService.php  - Core blockchain logic
└── Http/Controllers/
    ├── TransactionController.php  - Transaction API
    ├── BlockController.php        - Block mining API
    └── BlockchainController.php   - Validation API
```

### Frontend Structure (React)

```
frontend/src/
├── components/
│   └── Layout.jsx          - Main layout and navigation
├── pages/
│   ├── Dashboard.jsx       - Statistics and validation
│   ├── Transactions.jsx    - Transaction management
│   └── Blocks.jsx          - Blockchain viewer
└── services/
    └── api.js              - API communication
```

### Key Blockchain Functions

**Hash Calculation:**
```php
hash('sha256', $index . $previousHash . $timestamp . $transactions . $nonce)
```

**Proof of Work:**
```php
while (true) {
    $hash = calculateHash(..., $nonce);
    if (substr($hash, 0, $difficulty) === '00') break;
    $nonce++;
}
```

**Chain Validation:**
```php
foreach ($blocks as $block) {
    if ($block->current_hash !== calculateHash($block)) return false;
    if ($block->previous_hash !== $previousBlock->current_hash) return false;
}
```

---

## 📊 Project Configuration

### Blockchain Settings (backend/.env)

```env
# Difficulty: Number of leading zeros required in hash
# 2 = fast (seconds), 3 = medium, 4+ = slow (minutes)
BLOCKCHAIN_DIFFICULTY=2

# Database settings (pre-configured for Docker)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=blockchain
```

### Frontend Settings (frontend/.env)

```env
# Backend API URL
VITE_API_URL=http://localhost:8000/api/v1
```

---

## 🎤 Presentation Preparation

### Demo Checklist:

- [ ] Application running and accessible
- [ ] Create 3-5 transactions (show form)
- [ ] Mine a block (show mining process)
- [ ] Validate blockchain (show valid status)
- [ ] View blockchain (show blocks and transactions)
- [ ] Explain security (hash, proof of work, immutability)
- [ ] (Optional) Show tampering detection

### Talking Points:

1. **What is blockchain?** Linked chain of blocks using cryptographic hashes
2. **How are transactions created?** Users submit sender, receiver, amount
3. **What is mining?** Finding a nonce that produces a valid hash (proof of work)
4. **How is security ensured?** SHA256 hashing, chain linking, immutability
5. **How is tampering detected?** Hash validation and chain verification

---

## 📝 Assignment Submission Checklist

- [ ] Source code (complete project folder)
- [ ] Database migrations (in backend/database/migrations/)
- [ ] README.md explaining blockchain logic ✅
- [ ] Screenshots:
  - [ ] Transaction creation
  - [ ] Block mining
  - [ ] Blockchain validation (valid)
  - [ ] Blockchain validation (after tampering)
  - [ ] Complete blockchain view
- [ ] One-page written explanation of security (see SECURITY.md)

---

## 🎉 Congratulations!

You now have a fully functional blockchain application!

### Next Steps:

1. ✅ **Experiment** with the application
2. ✅ **Read** the documentation (README.md, SECURITY.md)
3. ✅ **Take** required screenshots
4. ✅ **Write** your security explanation
5. ✅ **Prepare** your presentation
6. ✅ **Submit** your assignment

---

## 🆘 Need Help?

### Documentation:
- **README.md** - Main guide
- **SECURITY.md** - Security details
- **QUICKSTART.md** - Quick reference
- **INSTALLATION.md** - Installation details

### Check Logs:
```bash
docker-compose logs        # All logs
docker logs blockchain_backend   # Backend only
docker logs blockchain_frontend  # Frontend only
docker logs blockchain_postgres  # Database only
```

### Reset Everything:
```bash
docker-compose down -v
docker-compose up --build -d
docker exec blockchain_backend php artisan migrate --force
docker exec blockchain_backend php artisan db:seed --force
```

---

## 📞 Support Commands

```bash
# Check container status
docker ps

# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Access backend shell
docker exec -it blockchain_backend bash

# Access frontend shell
docker exec -it blockchain_frontend sh

# Access database
docker exec -it blockchain_postgres psql -U blockchain_user -d blockchain

# Stop application
docker-compose stop

# Start application
docker-compose start

# Remove everything
docker-compose down -v
```

---

**Happy Blockchain Building! 🔗⛓️**

---

**Version**: 1.0  
**Last Updated**: October 26, 2025  
**Status**: Production Ready ✅
