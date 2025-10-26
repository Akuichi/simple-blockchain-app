# Blockchain Application - Architecture & Flow Diagrams

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER / BROWSER                          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ HTTP (Port 5173)
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    FRONTEND (React 18)                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  Dashboard   │  │ Transactions │  │  Blockchain  │         │
│  │    Page      │  │     Page     │  │     Page     │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                  │
│  ┌────────────────────────────────────────────────────┐        │
│  │         API Service (Axios)                        │        │
│  └────────────────────────────────────────────────────┘        │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ REST API (Port 8000)
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel 12)                         │
│                                                                  │
│  ┌────────────────────────────────────────────────────┐        │
│  │              API Controllers                       │        │
│  │  • TransactionController                           │        │
│  │  • BlockController                                 │        │
│  │  • BlockchainController                            │        │
│  └──────────────────────┬─────────────────────────────┘        │
│                         │                                       │
│  ┌──────────────────────▼─────────────────────────────┐        │
│  │         BlockchainService                          │        │
│  │  • calculateHash()                                 │        │
│  │  • mineBlock()                                     │        │
│  │  • proofOfWork()                                   │        │
│  │  • validateChain()                                 │        │
│  └──────────────────────┬─────────────────────────────┘        │
│                         │                                       │
│  ┌──────────────────────▼─────────────────────────────┐        │
│  │         Eloquent Models                            │        │
│  │  • Transaction Model                               │        │
│  │  • Block Model                                     │        │
│  └──────────────────────┬─────────────────────────────┘        │
└─────────────────────────┼─────────────────────────────────────┘
                          │
                          │ PostgreSQL Protocol (Port 5432)
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                  DATABASE (PostgreSQL 16)                       │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐ │
│  │ transactions │  │    blocks    │  │ block_transactions   │ │
│  │──────────────│  │──────────────│  │──────────────────────│ │
│  │ id           │  │ id           │  │ id                   │ │
│  │ sender       │  │ index_no     │  │ block_id (FK)        │ │
│  │ receiver     │  │ previous_hash│  │ transaction_id (FK)  │ │
│  │ amount       │  │ current_hash │  │ timestamps           │ │
│  │ status       │  │ nonce        │  └──────────────────────┘ │
│  │ timestamp    │  │ timestamp    │                            │
│  └──────────────┘  └──────────────┘                            │
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 Transaction Flow

```
1. CREATE TRANSACTION
   ┌──────────────────┐
   │ User fills form  │
   │ - Sender         │
   │ - Receiver       │
   │ - Amount         │
   └────────┬─────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ POST /api/v1/transaction    │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Validate Input              │
   │ - Amount > 0                │
   │ - Sender ≠ Receiver         │
   │ - All fields required       │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Save to Database            │
   │ Status: PENDING             │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Return Success              │
   └─────────────────────────────┘

2. MINE BLOCK
   ┌──────────────────┐
   │ User clicks      │
   │ "Mine Block"     │
   └────────┬─────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ POST /api/v1/block/mine     │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Get Pending Transactions    │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Get Last Block              │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Proof of Work               │
   │ Find valid nonce:           │
   │   nonce = 0                 │
   │   while(true):              │
   │     hash = SHA256(data)     │
   │     if hash starts with     │
   │        "00": break          │
   │     nonce++                 │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Create New Block            │
   │ - index = last + 1          │
   │ - previous_hash = last.hash │
   │ - current_hash = found hash │
   │ - nonce = found nonce       │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Link Transactions to Block  │
   │ Mark as MINED               │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Save Block to Database      │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Return Success + Block Data │
   └─────────────────────────────┘

3. VALIDATE BLOCKCHAIN
   ┌──────────────────┐
   │ User clicks      │
   │ "Validate Chain" │
   └────────┬─────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ GET /blockchain/validate    │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Get All Blocks (ordered)    │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ For Each Block:             │
   │                             │
   │ 1. Recalculate Hash         │
   │    hash = SHA256(           │
   │      index +                │
   │      previous_hash +        │
   │      timestamp +            │
   │      transactions +         │
   │      nonce                  │
   │    )                        │
   │                             │
   │ 2. Compare with Stored Hash │
   │    if (stored ≠ calculated) │
   │       INVALID!              │
   │                             │
   │ 3. Check Previous Hash Link │
   │    if (block.prev_hash ≠    │
   │        prev_block.hash)     │
   │       INVALID!              │
   │                             │
   │ 4. Verify Proof of Work     │
   │    if (!hash.startsWith     │
   │         "00"))              │
   │       INVALID!              │
   └────────┬────────────────────┘
            │
            ▼
   ┌─────────────────────────────┐
   │ Return Validation Result    │
   │ - valid: true/false         │
   │ - errors: [...]             │
   └─────────────────────────────┘
```

## 🔒 Security Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                       SECURITY LAYERS                           │
└─────────────────────────────────────────────────────────────────┘

Layer 1: INPUT VALIDATION
┌─────────────────────────────────────────────────────────────────┐
│ • Amount must be > 0                                            │
│ • Sender ≠ Receiver                                             │
│ • All fields required                                           │
│ • Numeric validation                                            │
│ • String length limits                                          │
└─────────────────────────────────────────────────────────────────┘
                              ▼
Layer 2: CRYPTOGRAPHIC HASHING
┌─────────────────────────────────────────────────────────────────┐
│ SHA256 Hash Calculation:                                        │
│                                                                  │
│  Input:  index + previous_hash + timestamp +                   │
│          transactions + nonce                                   │
│                                                                  │
│  Example:                                                       │
│  "1" + "abc123..." + "1635789012" +                            │
│  "[{sender:Alice...}]" + "42"                                  │
│                                                                  │
│  Output: "00a1b2c3d4e5f6789012345678901234567890123456789..."  │
│                                                                  │
│  Properties:                                                    │
│  ✓ Deterministic (same input = same output)                    │
│  ✓ Unique (different input = different output)                 │
│  ✓ One-way (cannot reverse)                                    │
│  ✓ Collision-resistant                                         │
└─────────────────────────────────────────────────────────────────┘
                              ▼
Layer 3: PROOF OF WORK
┌─────────────────────────────────────────────────────────────────┐
│ Mining Process:                                                 │
│                                                                  │
│  Difficulty = 2 (hash must start with "00")                    │
│                                                                  │
│  nonce = 0                                                      │
│  while (true):                                                  │
│    hash = calculateHash(data, nonce)                           │
│                                                                  │
│    nonce = 0 → hash = "a1b2c3..." ❌ (doesn't start with 00)   │
│    nonce = 1 → hash = "f9e8d7..." ❌                           │
│    nonce = 2 → hash = "3c4d5e..." ❌                           │
│    ...                                                          │
│    nonce = 42 → hash = "00abc1..." ✓ (valid!)                 │
│                                                                  │
│  Result: Computational work required to create block           │
└─────────────────────────────────────────────────────────────────┘
                              ▼
Layer 4: CHAIN LINKING
┌─────────────────────────────────────────────────────────────────┐
│ Block Chain Structure:                                          │
│                                                                  │
│  Block 0                    Block 1                    Block 2  │
│  ┌──────────────┐          ┌──────────────┐          ┌────────┐│
│  │ index: 0     │          │ index: 1     │          │ index:2││
│  │ prev: "0"    │──hash──▶ │ prev: abc... │──hash──▶ │ prev: d││
│  │ hash: abc... │          │ hash: def... │          │ hash: g││
│  │ nonce: 0     │          │ nonce: 42    │          │ nonce:1││
│  └──────────────┘          └──────────────┘          └────────┘│
│                                                                  │
│  Security: If Block 1 is modified:                             │
│  • Block 1's hash changes                                      │
│  • Block 2's prev_hash no longer matches                       │
│  • Chain breaks and validation fails                           │
└─────────────────────────────────────────────────────────────────┘
                              ▼
Layer 5: IMMUTABILITY
┌─────────────────────────────────────────────────────────────────┐
│ Database Constraints:                                           │
│                                                                  │
│ • No UPDATE operations on mined blocks                         │
│ • No DELETE operations allowed                                 │
│ • Transactions: pending → mined (one-way)                      │
│ • UNIQUE constraints on hashes                                 │
│ • FOREIGN KEY constraints                                      │
│ • Index constraints prevent duplicates                         │
└─────────────────────────────────────────────────────────────────┘
```

## 🔍 Tampering Detection Example

```
SCENARIO: Attacker modifies Block #1 transaction amount

BEFORE (Valid Chain):
┌─────────────────────────────────────────────────────────────────┐
│ Block 0                                                         │
│ hash: "00abc123..."                                            │
└────────────┬────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Block 1                                                         │
│ prev_hash: "00abc123..."                                       │
│ transactions: [{sender: "Alice", receiver: "Bob", amount: 100}]│
│ nonce: 42                                                       │
│ hash: "00def456..." ← Calculated from above data              │
└────────────┬────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Block 2                                                         │
│ prev_hash: "00def456..."                                       │
│ hash: "00ghi789..."                                            │
└─────────────────────────────────────────────────────────────────┘

AFTER TAMPERING (Invalid Chain):
┌─────────────────────────────────────────────────────────────────┐
│ Block 0                                                         │
│ hash: "00abc123..."                                            │
└────────────┬────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Block 1 ❌ TAMPERED                                            │
│ prev_hash: "00abc123..."                                       │
│ transactions: [{sender: "Alice", receiver: "Bob", amount: 999}]│
│               └─────────────────────────────────┬── CHANGED!   │
│ nonce: 42                                       │              │
│ hash: "00def456..." ← STORED (old hash)         │              │
│                                                  │              │
│ Validation Recalculates:                        │              │
│ new_hash = SHA256(0+"00abc123"+ts+[{...999}]+42)│              │
│ new_hash = "xyz789..." ← DIFFERENT!             │              │
│                                                  │              │
│ ERROR: Stored hash ≠ Calculated hash           │              │
└────────────┬────────────────────────────────────┴───────────────┘
             │ ❌ prev_hash mismatch!
             ▼
┌─────────────────────────────────────────────────────────────────┐
│ Block 2                                                         │
│ prev_hash: "00def456..." ← Looking for this                   │
│ But Block 1's actual hash is now "xyz789..."                  │
│ ERROR: Chain link broken!                                      │
└─────────────────────────────────────────────────────────────────┘

VALIDATION RESULT:
❌ Blockchain is INVALID
Errors:
- Block 1: Hash is invalid (tampered data detected)
- Block 2: Previous hash mismatch (chain broken)
```

## 📊 Data Flow Diagram

```
USER ACTIONS → API REQUESTS → BACKEND PROCESSING → DATABASE → RESPONSE

1. CREATE TRANSACTION:
   User Form → POST /transaction → Validate → Save to DB → Return Success
                                     ↓
                               Check Rules
                               (amount>0,
                               sender≠receiver)

2. MINE BLOCK:
   Button Click → POST /block/mine → Get Pending TXs → Calculate Hash
                                            ↓              ↓
                                       Get Last Block   Find Nonce (PoW)
                                            ↓              ↓
                                       Create Block ← Link TXs
                                            ↓
                                       Save to DB → Return Block

3. VALIDATE CHAIN:
   Button Click → GET /blockchain/validate → Get All Blocks
                                                   ↓
                                            For Each Block:
                                            - Recalc Hash
                                            - Check Prev Link
                                            - Verify PoW
                                                   ↓
                                            Return Valid/Invalid

4. VIEW BLOCKCHAIN:
   Page Load → GET /blocks → Query DB → Join Transactions → Return Data
                                                                  ↓
                                                          Frontend Renders
```

## 🎯 Component Interaction

```
┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND COMPONENTS                        │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐    │
│  │                    Layout.jsx                          │    │
│  │  ┌─────────────────────────────────────────────┐      │    │
│  │  │ Navigation: Dashboard | Transactions | Blocks│      │    │
│  │  └─────────────────────────────────────────────┘      │    │
│  │  ┌─────────────────────────────────────────────┐      │    │
│  │  │              Page Content                    │      │    │
│  │  │  ┌────────────────────────────────────┐     │      │    │
│  │  │  │ Dashboard.jsx                      │     │      │    │
│  │  │  │ - Stats cards                      │     │      │    │
│  │  │  │ - Validation status                │     │      │    │
│  │  │  │ - Mine block button                │────┐│      │    │
│  │  │  │ - Validate chain button            │    ││      │    │
│  │  │  └────────────────────────────────────┘    ││      │    │
│  │  │         OR                                  ││      │    │
│  │  │  ┌────────────────────────────────────┐    ││      │    │
│  │  │  │ Transactions.jsx                   │    ││      │    │
│  │  │  │ - Create form                      │────┼┤      │    │
│  │  │  │ - Pending list                     │    ││      │    │
│  │  │  │ - All transactions list            │    ││      │    │
│  │  │  └────────────────────────────────────┘    ││      │    │
│  │  │         OR                                  ││      │    │
│  │  │  ┌────────────────────────────────────┐    ││      │    │
│  │  │  │ Blocks.jsx                         │    ││      │    │
│  │  │  │ - Block list                       │────┘│      │    │
│  │  │  │ - Expandable details               │     │      │    │
│  │  │  │ - Transaction viewer               │     │      │    │
│  │  │  └────────────────────────────────────┘     │      │    │
│  │  └─────────────────────────────────────────────┘      │    │
│  └──────────────────────┬─────────────────────────────────┘    │
│                         │ API Calls                            │
│                         ▼                                       │
│  ┌────────────────────────────────────────────────────────┐    │
│  │             api.js (Axios Service)                     │    │
│  │  • createTransaction()                                 │    │
│  │  • getPendingTransactions()                            │    │
│  │  • mineBlock()                                         │    │
│  │  • validateBlockchain()                                │    │
│  │  • getAllBlocks()                                      │    │
│  └────────────────────────────────────────────────────────┘    │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTP Requests
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    BACKEND CONTROLLERS                          │
│                                                                  │
│  TransactionController        BlockController                   │
│  • create()                   • mine()                          │
│  • getPending()               • getAll()                        │
│  • getAll()                   • getById()                       │
│                                                                  │
│  BlockchainController                                           │
│  • validate()                                                   │
│  • getStats()                                                   │
│         │                                                        │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────┐       │
│  │        BlockchainService                            │       │
│  │  • calculateHash()                                  │       │
│  │  • mineBlock()                                      │       │
│  │  • proofOfWork()                                    │       │
│  │  • validateChain()                                  │       │
│  │  • getStats()                                       │       │
│  └─────────────────────────────────────────────────────┘       │
│         │                                                        │
│         ▼                                                        │
│  ┌─────────────────────────────────────────────────────┐       │
│  │     Models (Transaction, Block)                     │       │
│  └─────────────────────────────────────────────────────┘       │
└──────────────────────────┬──────────────────────────────────────┘
                           │ Database Queries
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                 PostgreSQL Database                             │
│  transactions ←→ block_transactions ←→ blocks                  │
└─────────────────────────────────────────────────────────────────┘
```

---

**Note**: These diagrams provide a visual overview of the application architecture, data flow, security mechanisms, and component interactions. Use them as a reference when understanding or explaining the system.
