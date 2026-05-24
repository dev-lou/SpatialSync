#!/bin/bash
# =============================================================================
# SpatialSync — Branch Name Validator
# Ensures branch names follow convention: type/description
# =============================================================================

BRANCH_NAME=$(git symbolic-ref --short HEAD 2>/dev/null)

if [ -z "$BRANCH_NAME" ]; then
  exit 0
fi

# Allow main, develop, and conventional branch names
if echo "$BRANCH_NAME" | grep -qE "^(main|develop|feature/|fix/|chore/|docs/|refactor/|test/|security/|release/|hotfix/)"; then
  exit 0
else
  echo "⚠️  Branch name '$BRANCH_NAME' doesn't follow convention."
  echo "   Use one of: feature/*, fix/*, chore/*, docs/*, refactor/*, test/*, security/*, release/*, hotfix/*"
  echo "   Example: feature/add-user-auth"
  exit 1
fi
