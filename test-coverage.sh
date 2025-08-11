#!/bin/bash

# Laravel Test Coverage Script
# This script runs PHPUnit tests with different coverage report formats

echo "🧪 Running Laravel Tests with Code Coverage..."
echo "================================================"

# Create coverage directory if it doesn't exist
mkdir -p coverage

# Function to check if Xdebug is enabled
check_coverage_driver() {
    # Try Homebrew PHP first (most likely to work)
    if [ -x "/opt/homebrew/bin/php" ] && /opt/homebrew/bin/php -m | grep -i xdebug > /dev/null; then
        echo "✅ Using Homebrew PHP with Xdebug"
        PHP_BINARY="/opt/homebrew/bin/php"
        XDEBUG_MODE="XDEBUG_MODE=coverage"
        return 0
    elif php -m | grep -i xdebug > /dev/null; then
        echo "✅ Using current PHP with Xdebug"
        PHP_BINARY="php"
        XDEBUG_MODE="XDEBUG_MODE=coverage"
        return 0
    elif php -m | grep -i pcov > /dev/null; then
        echo "✅ Using current PHP with PCOV"
        PHP_BINARY="php"
        XDEBUG_MODE=""
        return 0
    else
        echo "⚠️  No coverage driver found. Available options:"
        echo "   1. Install Xdebug: brew install xdebug"
        echo "   2. Install PCOV: pecl install pcov"
        echo "   3. Use Docker: ./test-with-docker.sh"
        echo ""
        echo "   Running tests without coverage..."
        $PHP_BINARY vendor/bin/phpunit
        exit 1
    fi
}

# Check for coverage driver
check_coverage_driver

echo ""
echo "📊 Generating coverage reports..."

# Run tests with HTML coverage report
echo "Generating HTML coverage report..."
env $XDEBUG_MODE $PHP_BINARY vendor/bin/phpunit --coverage-html coverage/html

# Run tests with text coverage report (console output)
echo ""
echo "Generating text coverage report..."
env $XDEBUG_MODE $PHP_BINARY vendor/bin/phpunit --coverage-text

# Run tests with Clover XML coverage report (useful for CI/CD)
echo ""
echo "Generating Clover XML coverage report..."
env $XDEBUG_MODE $PHP_BINARY vendor/bin/phpunit --coverage-clover coverage/clover.xml

# Run tests with PHP coverage report
echo ""
echo "Generating PHP coverage report..."
env $XDEBUG_MODE $PHP_BINARY vendor/bin/phpunit --coverage-php coverage/coverage.php

echo ""
echo "✅ Coverage reports generated successfully!"
echo "📁 HTML Report: coverage/html/index.html"
echo "📁 Clover XML: coverage/clover.xml"
echo "📁 PHP Report: coverage/coverage.php"
echo ""
echo "🌐 To view HTML report, open: coverage/html/index.html in your browser"
