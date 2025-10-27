# Simple Blockchain Application

A full-stack blockchain application built with Laravel 12, PostgreSQL, ReactJS, and Docker that demonstrates core blockchain principles including transaction management, block mining with proof of work, and chain validation.

## 🚀 Features

- **Transaction Management**: Create and manage blockchain transactions
- **Block Mining**: Simulate proof of work mining with configurable difficulty
- **Blockchain Validation**: Verify chain integrity and detect tampering
- **Tamper Detection**: Demonstrate blockchain immutability with interactive tamper/rebuild feature
- **Chain Rebuild**: Automatically fix tampered blocks by recalculating and re-mining
- **Immutability**: Once mined, blocks cannot be modified without detection
- **Real-time Dashboard**: Monitor blockchain statistics and status
- **Modern UI**: Clean, responsive interface built with React and TailwindCSS
- **Random Transactions**: Quick data generation for testing
- **Comprehensive Testing**: 98.2% test coverage (56/57 tests passing)

## 📚 Documentation

- **[HOW_TO_FIX_TAMPERED_CHAIN.md](HOW_TO_FIX_TAMPERED_CHAIN.md)** - Complete guide on fixing tampered blockchains with rebuild feature
- **[SECURITY_IMPLEMENTATION.md](SECURITY_IMPLEMENTATION.md)** - Complete security explanation covering SHA-256 hashing, chain linking, proof of work, and tamper detection
- **[TESTS_SUMMARY.md](TESTS_SUMMARY.md)** - Comprehensive test coverage documentation
- **[SECURITY.md](SECURITY.md)** - Security considerations and best practices
- **[QUICKSTART.md](QUICKSTART.md)** - Quick setup guide
- **[GETTING_STARTED.md](GETTING_STARTED.md)** - Detailed installation and usage guide

## 🏗️ Architecture

### Backend (Laravel 12)
- RESTful API endpoints for blockchain operations
- PostgreSQL database for persistent storage
- SHA256 cryptographic hashing
- Proof of Work implementation
- Transaction validation rules

### Frontend (React 18)
- Vite for fast development and building
- TailwindCSS for styling
- React Router for navigation
- Axios for API communication

### Database Schema
```
transactions
├── id (PK)
├── sender
├── receiver
├── amount
├── status (pending/mined)
├── timestamp
└── timestamps

blocks
├── id (PK)
├── index_no
├── previous_hash
├── current_hash
├── nonce
├── timestamp
└── timestamps

block_transactions (pivot)
├── id (PK)
├── block_id (FK)
├── transaction_id (FK)
└── timestamps
```

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **Docker Desktop** (v20.10 or higher)
- **Docker Compose** (v2.0 or higher)
- **Git** (for cloning the repository)

## 🔧 Installation Instructions

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd blockchain
```

### Step 2: Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Copy the environment file:
```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

3. The `.env` file is pre-configured for Docker. Key settings:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=blockchain
DB_USERNAME=blockchain_user
DB_PASSWORD=blockchain_pass
BLOCKCHAIN_DIFFICULTY=2
```

### Step 3: Frontend Setup

1. Navigate to the frontend directory:
```bash
cd ../frontend
```

2. Copy the environment file:
```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

### Step 4: Build and Start Docker Containers

1. Return to the project root:
```bash
cd ..
```

2. Build and start all containers:
```bash
docker-compose up --build
```

This command will:
- Pull required Docker images
- Build the Laravel backend container
- Build the React frontend container
- Start PostgreSQL database
- Install all dependencies

**Note**: Initial build may take 5-10 minutes depending on your internet connection.

### Step 5: Initialize the Database

Once containers are running, open a new terminal and run:

```bash
# Access the backend container
docker exec -it blockchain_backend bash

# Run migrations
php artisan migrate

# Create genesis block (first block in the chain)
php artisan db:seed

# Exit the container
exit
```

### Step 6: Access the Application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **PostgreSQL**: localhost:5432

## 🎯 Usage Guide

### Creating Transactions

1. Navigate to the **Transactions** page
2. Fill in the transaction form:
   - **Sender**: Name of the sender (e.g., "Alice")
   - **Receiver**: Name of the receiver (e.g., "Bob")
   - **Amount**: Amount to transfer (must be > 0)
3. Click **Create Transaction**
4. Transaction will be added to the pending pool

### Mining Blocks

1. Go to the **Dashboard** page
2. Ensure there are pending transactions
3. Click the **Mine Block** button
4. Wait for the proof of work to complete
5. The block will be added to the blockchain
6. All transactions in the block will be marked as "mined"

### Validating the Blockchain

1. On the **Dashboard**, click **Validate Chain**
2. The system will verify:
   - Each block's hash is valid
   - Previous hash links are correct
   - Proof of work difficulty is met
3. Results will display:
   - ✅ Green: Blockchain is valid and secure
   - ⚠️ Red: Blockchain has been tampered with

### Viewing the Blockchain

1. Navigate to the **Blockchain** page
2. View all blocks in chronological order
3. Click on any block to expand and see:
   - Block hash and previous hash
   - Nonce value used for mining
   - All transactions included in the block
4. Genesis block (Block #0) is highlighted in purple

## 🔐 Security Implementation

### Hash Generation
- Uses SHA256 cryptographic function
- Hash includes: index, previous hash, timestamp, transactions, and nonce
- Example: `hash('sha256', $index . $previousHash . $timestamp . $transactions . $nonce)`

### Proof of Work
- Configurable difficulty (default: 2)
- Miners must find a nonce where the hash starts with N zeros
- Example: Hash must start with "00" for difficulty 2
- Prevents rapid block creation and tampering

### Data Validation
```php
// Transaction validation rules
- sender: required, string, max 255 characters
- receiver: required, string, must be different from sender
- amount: required, numeric, must be greater than 0
```

### Immutability
- Blocks cannot be edited or deleted once mined
- Any tampering changes the hash, breaking the chain
- Validation detects broken links immediately

### Chain Validation Algorithm
```
1. For each block in the chain:
   a. Verify current hash is correct by recalculating
   b. Verify previous hash matches previous block's current hash
   c. Verify hash meets difficulty requirement
2. If any check fails, chain is invalid
```

## 🛠️ Development Commands

### Backend (Laravel)

```bash
# Access backend container
docker exec -it blockchain_backend bash

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database (creates genesis block)
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear

# Run tests (if implemented)
php artisan test
```

### Frontend (React)

```bash
# Access frontend container
docker exec -it blockchain_frontend sh

# Install new package
yarn add <package-name>

# Build for production
yarn build

# Preview production build
yarn preview
```

### Database

```bash
# Access PostgreSQL
docker exec -it blockchain_postgres psql -U blockchain_user -d blockchain

# Useful PostgreSQL commands:
\dt          # List all tables
\d blocks    # Describe blocks table
SELECT * FROM blocks;
SELECT * FROM transactions;
\q           # Quit
```

## 📡 API Endpoints

### Transactions
```
POST   /api/v1/transaction           - Create a new transaction
GET    /api/v1/transactions/pending  - Get all pending transactions
GET    /api/v1/transactions          - Get all transactions
```

### Blocks
```
POST   /api/v1/block/mine            - Mine a new block
GET    /api/v1/blocks                - Get all blocks
GET    /api/v1/blocks/{id}           - Get specific block
```

### Blockchain
```
GET    /api/v1/blockchain/validate   - Validate entire blockchain
GET    /api/v1/blockchain/stats      - Get blockchain statistics
```

### Example API Requests

**Create Transaction:**
```bash
curl -X POST http://localhost:8000/api/v1/transaction \
  -H "Content-Type: application/json" \
  -d '{
    "sender": "Alice",
    "receiver": "Bob",
    "amount": 100.50
  }'
```

**Mine Block:**
```bash
curl -X POST http://localhost:8000/api/v1/block/mine
```

**Validate Blockchain:**
```bash
curl http://localhost:8000/api/v1/blockchain/validate
```

## 🐛 Troubleshooting

### Containers won't start
```bash
# Stop all containers
docker-compose down

# Remove volumes
docker-compose down -v

# Rebuild and start
docker-compose up --build
```

### Database connection errors
```bash
# Check if PostgreSQL is running
docker ps | grep postgres

# Check database logs
docker logs blockchain_postgres

# Restart PostgreSQL
docker restart blockchain_postgres
```

### Frontend can't connect to backend
1. Verify backend is running: http://localhost:8000
2. Check CORS settings in `backend/config/cors.php`
3. Ensure API URL in frontend matches: `http://localhost:8000/api/v1`

### Migrations fail
```bash
# Access backend container
docker exec -it blockchain_backend bash

# Reset database
php artisan migrate:fresh

# Seed genesis block
php artisan db:seed
```

## 📦 Project Structure

```
blockchain/
├── backend/                      # Laravel 12 backend
│   ├── app/
│   │   ├── Http/Controllers/    # API controllers
│   │   ├── Models/              # Eloquent models
│   │   └── Services/            # Business logic (BlockchainService)
│   ├── config/                  # Configuration files
│   ├── database/
│   │   ├── migrations/          # Database migrations
│   │   └── seeders/             # Database seeders
│   ├── routes/
│   │   └── api.php              # API routes
│   ├── .env.example             # Environment template
│   ├── Dockerfile               # Backend container config
│   └── composer.json            # PHP dependencies
│
├── frontend/                     # React 18 frontend
│   ├── src/
│   │   ├── components/          # Reusable components
│   │   ├── pages/               # Page components
│   │   ├── services/            # API service
│   │   ├── App.jsx              # Main app component
│   │   └── main.jsx             # Entry point
│   ├── .env.example             # Environment template
│   ├── Dockerfile               # Frontend container config
│   ├── package.json             # Node dependencies
│   ├── vite.config.js           # Vite configuration
│   └── tailwind.config.js       # Tailwind CSS config
│
├── docker-compose.yml            # Docker orchestration
├── .gitignore                    # Git ignore rules
└── README.md                     # This file
```

## 🧪 Testing the Application

### Test Scenario 1: Normal Flow
1. Create 3-5 transactions
2. Mine a block
3. Validate the blockchain (should be valid)
4. View the blockchain to see the new block

### Test Scenario 2: Detect Tampering
1. Create and mine a block
2. Manually modify a block in the database:
```sql
docker exec -it blockchain_postgres psql -U blockchain_user -d blockchain
UPDATE blocks SET current_hash = 'tampered_hash' WHERE index_no = 1;
\q
```
3. Validate the blockchain (should be invalid)
4. View the error messages explaining what's wrong

### Test Scenario 3: Multiple Blocks
1. Create 10 transactions
2. Mine a block (includes all pending)
3. Create 5 more transactions
4. Mine another block
5. Verify chain links correctly

## 🎓 Blockchain Principles Demonstrated

### 1. Immutability
Once a block is mined, it cannot be changed without breaking the chain. Any modification to a block changes its hash, which breaks the link to subsequent blocks.

### 2. Chain Validation
Each block references the previous block's hash, creating an unbreakable chain. If any block is tampered with, the validation fails.

### 3. Proof of Work
Mining requires computational work to find a valid hash. This prevents rapid block creation and provides security through computational cost.

### 4. Transparency
All transactions and blocks are visible and verifiable. Anyone can validate the entire blockchain history.

### 5. Decentralization (Simplified)
While this is a single-node implementation, the architecture demonstrates how blockchain data could be distributed and synchronized across multiple nodes.

## 📊 Performance Considerations

### Mining Time
- Difficulty 2: ~instant to few seconds
- Difficulty 3: ~few seconds
- Difficulty 4: ~several seconds to minutes
- Difficulty 5+: ~minutes to hours

**Note**: Higher difficulty increases security but also increases mining time.

### Database Optimization
- Indexes on frequently queried fields
- Eager loading of relationships
- Proper foreign key constraints

## 🔄 Stopping the Application

```bash
# Stop containers (keeps data)
docker-compose stop

# Stop and remove containers (keeps data in volumes)
docker-compose down

# Stop, remove containers and volumes (deletes all data)
docker-compose down -v
```

## 🤝 Contributing

This is an educational project. Suggestions for improvement:
- Add user authentication
- Implement multiple node simulation
- Add transaction signatures
- Implement Merkle trees
- Add block rewards
- Create mining difficulty adjustment algorithm

## 📄 License

This project is created for educational purposes.
---

**Note**: This is a simplified blockchain implementation for educational purposes. Production blockchain systems require additional security measures, consensus mechanisms, and network protocols.
