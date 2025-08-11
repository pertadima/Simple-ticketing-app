#!/bin/bash

# Simple Laravel Test Runner
# This script runs tests without coverage requirements

echo "🧪 Running Laravel Tests..."
echo "=========================="

# Run basic tests first
echo "Running all tests..."
./vendor/bin/phpunit

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ All tests passed!"
    echo ""
    echo "📋 Test Summary:"
    echo "To run tests with coverage, you need to install either:"
    echo "1. Xdebug: brew install sh"
    echo "2. PCOV: pecl install pcov"
    echo ""
    echo "After installation, use: ./test-coverage.sh"
else
    echo ""
    echo "❌ Some tests failed. Please check the output above."
fi
