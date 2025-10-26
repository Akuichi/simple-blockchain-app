#!/usr/bin/env bash

echo "================================"
echo "Blockchain Application Setup"
echo "================================"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker Desktop first."
    echo "   Download from: https://www.docker.com/products/docker-desktop"
    exit 1
fi

echo "✓ Docker is installed"

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed."
    exit 1
fi

echo "✓ Docker Compose is installed"
echo ""

# Copy environment files if they don't exist
if [ ! -f backend/.env ]; then
    echo "📝 Creating backend .env file..."
    cp backend/.env.example backend/.env
    echo "✓ Backend .env created"
else
    echo "✓ Backend .env already exists"
fi

if [ ! -f frontend/.env ]; then
    echo "📝 Creating frontend .env file..."
    cp frontend/.env.example frontend/.env
    echo "✓ Frontend .env created"
else
    echo "✓ Frontend .env already exists"
fi

echo ""
echo "🐳 Building and starting Docker containers..."
echo "   This may take 5-10 minutes on first run..."
echo ""

# Build and start containers
docker-compose up --build -d

echo ""
echo "⏳ Waiting for database to be ready..."
sleep 10

echo ""
echo "🗄️  Running database migrations..."
docker exec blockchain_backend php artisan migrate --force

echo ""
echo "🌱 Seeding database (creating genesis block)..."
docker exec blockchain_backend php artisan db:seed --force

echo ""
echo "================================"
echo "✅ Setup Complete!"
echo "================================"
echo ""
echo "🌐 Application URLs:"
echo "   Frontend: http://localhost:5173"
echo "   Backend:  http://localhost:8000"
echo "   Database: localhost:5432"
echo ""
echo "📚 Next steps:"
echo "   1. Open http://localhost:5173 in your browser"
echo "   2. Create some transactions"
echo "   3. Mine a block"
echo "   4. Validate the blockchain"
echo ""
echo "🛑 To stop: docker-compose stop"
echo "🗑️  To remove: docker-compose down"
echo ""
