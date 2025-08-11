#!/bin/bash

# Laravel Test Coverage Script
# This script runs PHPUnit tests with different coverage report formats

echo "🧪 Running Laravel Tests with Code Coverage..."
echo "================================================"

# Create coverage directory if it doesn't exist
mkdir -p coverage

# Function to check if Xdebug is enabled
check_xdebug() {
    if php -m | grep -i xdebug > /dev/null; then
        echo "✅ Xdebug is enabled"
    elif php -m | grep -i pcov > /dev/null; then
        echo "✅ PCOV is enabled"
    else
        echo "⚠️  Neither Xdebug nor PCOV is enabled. Code coverage requires one of these extensions."
        echo "   To install Xdebug on macOS:"
        echo "   Method 1 (PECL): pecl install xdebug"
        echo "   Method 2 (Homebrew): brew install xdebug"
        echo "   Method 3 (PCOV - faster): pecl install pcov"
        echo ""
        echo "   After installation, restart your terminal and try again."
        exit 1
    fi
}

# Check if Xdebug is available
check_xdebug

echo ""
echo "📊 Generating coverage reports..."

# Run tests with HTML coverage report
echo "Generating HTML coverage report..."
./vendor/bin/phpunit --coverage-html coverage/html

# Run tests with text coverage report (console output)
echo ""
echo "Generating text coverage report..."
./vendor/bin/phpunit --coverage-text

# Run tests with Clover XML coverage report (useful for CI/CD)
echo ""
echo "Generating Clover XML coverage report..."
./vendor/bin/phpunit --coverage-clover coverage/clover.xml

# Run tests with PHP coverage report
echo ""
echo "Generating PHP coverage report..."
./vendor/bin/phpunit --coverage-php coverage/coverage.php

echo ""
echo "✅ Coverage reports generated successfully!"
echo "📁 HTML Report: coverage/html/index.html"
echo "📁 Clover XML: coverage/clover.xml"
echo "📁 PHP Report: coverage/coverage.php"
echo ""
echo "🌐 To view HTML report, open: coverage/html/index.html in your browser"
