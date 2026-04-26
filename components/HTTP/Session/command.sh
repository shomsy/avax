#!/usr/bin/env bash
set -e

echo "🚀 Starting enterprise Session v5 structure setup..."

# 📍 Automatski pronađi root Avax folder
BASE_DIR=$(find . -type d -path "*/Avax/HTTP/Session" | head -n 1)

if [ -z "$BASE_DIR" ]; then
  echo "❌ Could not find Avax/HTTP/Session directory!"
  exit 1
fi

echo "📁 Found Session base at: $BASE_DIR"

# 1️⃣ Kreiraj novu enterprise strukturu
mkdir -p v5/Avax/HTTP/Session/{Core/{Config,Data,Lifecycle},Recovery/Data,Audit/Features,Events/Features}

# 2️⃣ Kopiraj postojeće fajlove u nove module
echo "📦 Copying files into v5 structure..."

# --- Core ---
cp -v "$BASE_DIR/Config/SessionConfig.php" v5/Avax/HTTP/Session/Core/Config/ 2>/dev/null || true
cp -v "$BASE_DIR/Data/FileStore.php" v5/Avax/HTTP/Session/Core/Data/ 2>/dev/null || true
cp -v "$BASE_DIR/Data/"*".php" v5/Avax/HTTP/Session/Core/Data/ 2>/dev/null || true
cp -v "$BASE_DIR/Lifecycle/SessionProvider.php" v5/Avax/HTTP/Session/Core/Lifecycle/ 2>/dev/null || true

# --- Recovery ---
cp -v "$BASE_DIR/Data/Recovery.php" v5/Avax/HTTP/Session/Recovery/Data/ 2>/dev/null || true

# --- Audit ---
cp -v "$BASE_DIR/Features/Audit.php" v5/Avax/HTTP/Session/Audit/Features/ 2>/dev/null || true

# --- Events ---
cp -v "$BASE_DIR/Features/Events.php" v5/Avax/HTTP/Session/Events/Features/ 2>/dev/null || true
cp -v "$BASE_DIR/Features/AsyncEventDispatcher.php" v5/Avax/HTTP/Session/Events/Features/ 2>/dev/null || true

# --- Session Root (Facade) ---
cp -v "$BASE_DIR/Session.php" v5/Avax/HTTP/Session/ 2>/dev/null || true

# 3️⃣ Kreiraj Manager fajlove (ako ne postoje)
echo "🧠 Generating Manager placeholders..."
touch v5/Avax/HTTP/Session/Core/CoreManager.php
touch v5/Avax/HTTP/Session/Recovery/RecoveryManager.php
touch v5/Avax/HTTP/Session/Audit/AuditManager.php
touch v5/Avax/HTTP/Session/Events/EventsManager.php

# 4️⃣ Prikaži finalnu strukturu (fallback ako tree ne postoji)
echo "✅ Enterprise structure created successfully:"
find v5/Avax/HTTP/Session -type f | sort
