# Xdebug Configuration for Herd Lite

## The Problem
You're using Herd Lite, which provides its own PHP binary and configuration. When you installed Xdebug using `pecl install xdebug`, it was installed for the system PHP, not Herd Lite's PHP.

## Solutions

### Option 1: Enable Xdebug in Herd Lite (Recommended)

1. **Check if Herd Lite has Xdebug available:**
   ```bash
   # Check what PHP extensions are available in Herd Lite
   /Users/irfan.pertadima/.config/herd-lite/bin/php -m | grep xdebug
   ```

2. **Edit Herd Lite's php.ini file:**
   ```bash
   # Open the Herd Lite php.ini file
   nano /Users/irfan.pertadima/.config/herd-lite/bin/php.ini
   ```

3. **Add Xdebug configuration to the php.ini file:**
   ```ini
   ; Add these lines to enable Xdebug
   zend_extension=xdebug
   xdebug.mode=coverage
   xdebug.start_with_request=yes
   ```

### Option 2: Use System PHP Instead of Herd Lite

1. **Check your system PHP:**
   ```bash
   # Use system PHP instead
   /usr/bin/php -v
   /usr/bin/php -m | grep xdebug
   ```

2. **Run tests with system PHP:**
   ```bash
   # Use system PHP for testing
   /usr/bin/php vendor/bin/phpunit --coverage-html coverage/html
   ```

### Option 3: Install PCOV for Herd Lite (Easier Alternative)

PCOV is often easier to set up and faster than Xdebug for coverage:

1. **Add PCOV to Herd Lite's php.ini:**
   ```bash
   echo "extension=pcov" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini
   echo "pcov.enabled=1" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini
   ```

### Option 4: Use Homebrew PHP

1. **Install PHP via Homebrew:**
   ```bash
   brew install php
   brew install xdebug
   ```

2. **Use Homebrew PHP for testing:**
   ```bash
   # Use Homebrew PHP
   /opt/homebrew/bin/php vendor/bin/phpunit --coverage-html coverage/html
   ```

## Quick Fix Commands

Try these commands in order:

```bash
# 1. First, try adding Xdebug to Herd Lite's php.ini
echo "zend_extension=xdebug" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini
echo "xdebug.mode=coverage" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini

# 2. Test if it works
php -m | grep xdebug

# 3. If that doesn't work, try PCOV instead
echo "extension=pcov" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini
echo "pcov.enabled=1" >> /Users/irfan.pertadima/.config/herd-lite/bin/php.ini

# 4. Test PCOV
php -m | grep pcov
```

## Testing the Fix

After applying any of the above solutions, test with:

```bash
# Check if coverage extension is now available
php -m | grep -E "(xdebug|pcov)"

# Run the coverage script
./test-coverage.sh

# Or run tests directly
./vendor/bin/phpunit --coverage-text
```
