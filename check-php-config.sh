#!/bin/bash

echo "🔍 PHP Configuration Diagnostics"
echo "================================="

echo ""
echo "📋 PHP Version:"
php -v

echo ""
echo "📋 PHP Modules (checking for Xdebug/PCOV):"
php -m | grep -E "(xdebug|pcov)" || echo "❌ Neither Xdebug nor PCOV found in loaded modules"

echo ""
echo "📋 PHP Configuration File Location:"
php --ini

echo ""
echo "📋 PHP Extension Directory:"
php -i | grep extension_dir

echo ""
echo "📋 Checking if Xdebug extension file exists:"
EXT_DIR=$(php -r "echo ini_get('extension_dir');")
echo "Extension directory: $EXT_DIR"
if [ -f "$EXT_DIR/xdebug.so" ]; then
    echo "✅ xdebug.so found in extension directory"
else
    echo "❌ xdebug.so NOT found in extension directory"
fi

echo ""
echo "📋 Checking for Xdebug configuration in php.ini:"
php -i | grep -i xdebug || echo "❌ No Xdebug configuration found"

echo ""
echo "📋 Suggested fixes:"
echo "1. Check if Xdebug is properly loaded:"
echo "   php -m | grep xdebug"
echo ""
echo "2. If Xdebug is installed but not loaded, add to php.ini:"
echo "   zend_extension=xdebug"
echo ""
echo "3. Find your php.ini file and edit it:"
echo "   php --ini"
echo ""
echo "4. After editing php.ini, restart your web server/PHP-FPM"
