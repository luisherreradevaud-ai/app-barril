#!/bin/bash

# AJAX Security Migration Status Checker
# This script checks which AJAX endpoints have been migrated to the new security pattern

echo "=========================================="
echo "AJAX Security Migration Status"
echo "=========================================="
echo ""

AJAX_DIR="."
TOTAL=0
MIGRATED=0
NOT_MIGRATED=0

echo "Checking all AJAX endpoints..."
echo ""

# Check for migrated files (using AjaxSecurity)
echo "✅ MIGRATED ENDPOINTS:"
echo "---"
for file in "$AJAX_DIR"/ajax_*.php; do
  if [ -f "$file" ]; then
    TOTAL=$((TOTAL + 1))
    if grep -q "AjaxSecurity::init" "$file"; then
      MIGRATED=$((MIGRATED + 1))
      basename "$file"
    fi
  fi
done

echo ""
echo "❌ NOT MIGRATED (Still using old pattern):"
echo "---"
for file in "$AJAX_DIR"/ajax_*.php; do
  if [ -f "$file" ]; then
    if ! grep -q "AjaxSecurity::init" "$file"; then
      NOT_MIGRATED=$((NOT_MIGRATED + 1))
      basename "$file"
    fi
  fi
done

echo ""
echo "=========================================="
echo "SUMMARY"
echo "=========================================="
echo "Total endpoints:      $TOTAL"
echo "Migrated:            $MIGRATED"
echo "Not migrated:        $NOT_MIGRATED"
echo ""

if [ $NOT_MIGRATED -eq 0 ]; then
  echo "🎉 All endpoints have been migrated!"
else
  PERCENT=$((MIGRATED * 100 / TOTAL))
  echo "Progress: $PERCENT% complete"
  echo ""
  echo "Run this script again after migrating more endpoints."
fi

echo ""
echo "=========================================="
