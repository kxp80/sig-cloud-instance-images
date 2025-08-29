#!/bin/bash

# Gallery Server Startup Script

echo "🚀 Starting Advanced Social Gallery Server..."
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP ist nicht installiert!"
    echo "Bitte installieren Sie PHP 7.4 oder höher."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

# Create data directory if it doesn't exist
if [ ! -d "data" ]; then
    echo "📁 Erstelle data/ Verzeichnis..."
    mkdir -p data
    chmod 755 data
fi

# Set proper permissions
echo "🔐 Setze Berechtigungen..."
chmod 755 api/
chmod 644 api/*.php

# Check if port 8000 is available
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Port 8000 ist bereits belegt."
    echo "Versuche Port 8001..."
    PORT=8001
else
    PORT=8000
fi

echo ""
echo "🌐 Server wird gestartet auf: http://localhost:$PORT"
echo "📱 Gallery: http://localhost:$PORT/index.html"
echo "🧪 API Test: http://localhost:$PORT/test_api.php"
echo ""
echo "Drücken Sie Ctrl+C zum Beenden."
echo ""

# Start PHP server
php -S localhost:$PORT