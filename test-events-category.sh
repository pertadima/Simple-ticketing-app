#!/bin/bash

echo "🧪 Running EventsCategoryController Tests"
echo "=========================================="

echo "📋 Running Feature Tests..."
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Feature/EventsCategoryControllerTest.php --testdox

echo ""
echo "📋 Running Unit Tests..."
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Unit/EventsCategoryControllerUnitTest.php --testdox

echo ""
echo "📋 Running Tests with Coverage..."
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Feature/EventsCategoryControllerTest.php tests/Unit/EventsCategoryControllerUnitTest.php --coverage-text

echo ""
echo "✅ EventsCategoryController tests completed!"
