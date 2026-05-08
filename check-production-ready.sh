#!/bin/bash

# Production Readiness Checklist for BlueprintFlow
# Run this before deploying to Render.com

echo "🔍 BlueprintFlow Production Readiness Check"
echo "=========================================="
echo ""

# Check 1: package.json dependencies
echo "✓ Checking npm dependencies..."
if grep -q "alpinejs" package.json; then
    echo "  ✅ alpinejs is installed"
else
    echo "  ❌ CRITICAL: alpinejs not in package.json"
    exit 1
fi

# Check 2: Build process
echo ""
echo "✓ Verifying production build..."
if npm run build > /tmp/build.log 2>&1; then
    echo "  ✅ Build successful"
    BUILD_SIZE=$(du -sh public/build | cut -f1)
    echo "  📦 Build artifact size: $BUILD_SIZE"
else
    echo "  ❌ Build failed"
    cat /tmp/build.log
    exit 1
fi

# Check 3: Environment variables
echo ""
echo "✓ Checking environment configuration..."
ENV_VARS=(
    "APP_ENV"
    "APP_KEY"
    "APP_NAME"
    "SUPABASE_URL"
    "SUPABASE_SERVICE_KEY"
    "SUPABASE_ANON_KEY"
    "DB_CONNECTION"
    "DB_HOST"
    "DB_DATABASE"
    "DB_USERNAME"
    "DB_PASSWORD"
)

MISSING_VARS=0
if [ -f ".env" ]; then
    for VAR in "${ENV_VARS[@]}"; do
        if grep -q "^$VAR=" .env; then
            echo "  ✅ $VAR configured"
        else
            echo "  ⚠️  $VAR not found (will be set in Render)"
        fi
    done
else
    echo "  ⚠️  .env file not found (expected for CI/CD)"
fi

# Check 4: .env in .gitignore
echo ""
echo "✓ Checking .gitignore..."
if grep -q "^.env$" .gitignore; then
    echo "  ✅ .env properly ignored"
else
    echo "  ❌ WARNING: .env should be in .gitignore"
fi

# Check 5: Docker configuration
echo ""
echo "✓ Checking Docker setup..."
if [ -f "Dockerfile" ]; then
    echo "  ✅ Dockerfile present"
    if grep -q "alpinejs\|npm install" Dockerfile; then
        echo "  ✅ npm install configured in Docker"
    fi
    if grep -q "npm run build" Dockerfile; then
        echo "  ✅ npm build configured in Docker"
    fi
else
    echo "  ❌ Dockerfile missing"
    exit 1
fi

if [ -f ".dockerignore" ]; then
    echo "  ✅ .dockerignore present"
else
    echo "  ⚠️  .dockerignore missing (but not critical)"
fi

# Check 6: Security audit
echo ""
echo "✓ Running security audit..."
npm audit --production > /tmp/audit.log 2>&1
VULN_COUNT=$(grep -c "vulnerabilities" /tmp/audit.log)
if [ $VULN_COUNT -gt 0 ]; then
    echo "  ⚠️  Some vulnerabilities detected (review audit.log)"
    echo "  Run: npm audit"
else
    echo "  ✅ No vulnerabilities in production dependencies"
fi

# Check 7: Git status
echo ""
echo "✓ Checking Git status..."
if git status --porcelain | grep -q "^??"; then
    echo "  ⚠️  Untracked files present"
    git status --porcelain | grep "^??" | head -5
fi

if git status --porcelain | grep -q "^ M"; then
    echo "  ⚠️  Modified files detected"
    git status --porcelain | grep "^ M" | head -5
fi

if [ -z "$(git status --porcelain)" ]; then
    echo "  ✅ Working directory clean"
fi

# Check 8: Render.yaml
echo ""
echo "✓ Checking Render configuration..."
if [ -f "render.yaml" ]; then
    echo "  ✅ render.yaml present"
else
    echo "  ⚠️  render.yaml not found (but not required)"
fi

# Check 9: Laravel config
echo ""
echo "✓ Checking Laravel configuration..."
if [ -f "config/app.php" ]; then
    echo "  ✅ Laravel config present"
fi

# Summary
echo ""
echo "=========================================="
echo "✅ Production Readiness Check Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Commit changes: git add . && git commit -m 'Production ready'"
echo "2. Push to GitHub: git push origin main"
echo "3. Create Web Service on Render.com"
echo "4. Set environment variables in Render Dashboard"
echo "5. Monitor deployment logs"
echo ""
echo "📖 See DEPLOYMENT.md for detailed instructions"
