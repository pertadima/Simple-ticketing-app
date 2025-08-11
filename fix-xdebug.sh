#!/bin/bash

echo "🔧 Fixing Xdebug for Herd Lite PHP"
echo "=================================="

HERD_PHP_INI="/Users/irfan.pertadima/.config/herd-lite/bin/php.ini"

echo "📋 Current PHP configuration:"
echo "PHP Binary: $(which php)"
echo "PHP Version: $(php -v | head -n 1)"
echo "PHP INI: $HERD_PHP_INI"

echo ""
echo "📋 Current php.ini content:"
if [ -f "$HERD_PHP_INI" ]; then
    cat "$HERD_PHP_INI"
else
    echo "❌ PHP INI file not found at $HERD_PHP_INI"
    exit 1
fi

echo ""
echo "🔧 Adding Xdebug configuration..."

# Check if Xdebug is already configured
if grep -q "xdebug" "$HERD_PHP_INI"; then
    echo "⚠️  Xdebug configuration already exists in php.ini"
else
    echo "➕ Adding Xdebug configuration to php.ini..."
    echo "" >> "$HERD_PHP_INI"
    echo "; Xdebug Configuration for Code Coverage" >> "$HERD_PHP_INI"
    echo "zend_extension=xdebug" >> "$HERD_PHP_INI"
    echo "xdebug.mode=coverage" >> "$HERD_PHP_INI"
    echo "xdebug.start_with_request=yes" >> "$HERD_PHP_INI"
    echo "✅ Xdebug configuration added"
fi

echo ""
echo "🧪 Testing Xdebug installation..."
if php -m | grep -i xdebug > /dev/null; then
    echo "✅ Xdebug is now loaded!"
else
    echo "❌ Xdebug is still not loaded. Trying PCOV instead..."
    
    # Try PCOV as fallback
    if ! grep -q "pcov" "$HERD_PHP_INI"; then
        echo "➕ Adding PCOV configuration..."
        echo "" >> "$HERD_PHP_INI"
        echo "; PCOV Configuration for Code Coverage" >> "$HERD_PHP_INI"
        echo "extension=pcov" >> "$HERD_PHP_INI"
        echo "pcov.enabled=1" >> "$HERD_PHP_INI"
        echo "pcov.directory=app" >> "$HERD_PHP_INI"
    fi
    
    if php -m | grep -i pcov > /dev/null; then
        echo "✅ PCOV is now loaded!"
    else
        echo "❌ Neither Xdebug nor PCOV could be loaded."
        echo "   You may need to install the extensions manually."
        echo ""
        echo "Alternative solutions:"
        echo "1. Use Homebrew PHP: brew install php xdebug"
        echo "2. Use system PHP: /usr/bin/php"
        echo "3. Check Herd Lite documentation for extension support"
        exit 1
    fi
fi

echo ""
echo "📋 Updated php.ini content:"
cat "$HERD_PHP_INI"

echo ""
echo "🧪 Final test - running a simple coverage check..."
./vendor/bin/phpunit --version
php -m | grep -E "(xdebug|pcov)" && echo "✅ Coverage extension is available!" || echo "❌ No coverage extension found"

echo ""
echo "🎉 Setup complete! You can now run:"
echo "   ./test-coverage.sh"
echo "   or"
echo "   ./vendor/bin/phpunit --coverage-html coverage/html"
