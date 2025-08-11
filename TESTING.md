# Laravel Testing with Code Coverage Guide

## Prerequisites

To generate code coverage reports, you need either **Xdebug** or **PCOV** PHP extensions installed.

### Installing Xdebug (Recommended for Development)

#### Option 1: Using Homebrew (macOS)
```bash
# For PHP 8.2
brew install php@8.2-xdebug

# For PHP 8.3
brew install php@8.3-xdebug
```

#### Option 2: Using PECL
```bash
pecl install xdebug
```

#### Option 3: Installing PCOV (Faster alternative)
```bash
pecl install pcov
```

### Verifying Installation
```bash
# Check if Xdebug is installed
php -m | grep xdebug

# Check if PCOV is installed
php -m | grep pcov
```

## Running Tests with Coverage

### Method 1: Using Composer Scripts (Recommended)

```bash
# Run all tests
composer run test

# Generate HTML coverage report
composer run test-coverage

# Generate text coverage report (console)
composer run test-coverage-text

# Generate Clover XML coverage report
composer run test-coverage-clover
```

### Method 2: Using PHPUnit Directly

```bash
# Basic test run
./vendor/bin/phpunit

# HTML coverage report
./vendor/bin/phpunit --coverage-html coverage/html

# Text coverage report
./vendor/bin/phpunit --coverage-text

# Clover XML coverage report
./vendor/bin/phpunit --coverage-clover coverage/clover.xml

# PHP coverage report
./vendor/bin/phpunit --coverage-php coverage/coverage.php
```

### Method 3: Using the Custom Script

```bash
# Run the comprehensive coverage script
./test-coverage.sh
```

## Coverage Report Types

### 1. HTML Report
- **Location**: `coverage/html/index.html`
- **Usage**: Open in browser for interactive coverage analysis
- **Best for**: Detailed analysis, visual coverage inspection

### 2. Text Report
- **Output**: Console
- **Usage**: Quick coverage overview in terminal
- **Best for**: CI/CD pipelines, quick checks

### 3. Clover XML Report
- **Location**: `coverage/clover.xml`
- **Usage**: Machine-readable format
- **Best for**: CI/CD integration, coverage badges

### 4. PHP Report
- **Location**: `coverage/coverage.php`
- **Usage**: Programmatic analysis
- **Best for**: Custom coverage analysis scripts

## Running Specific Tests

```bash
# Run only Feature tests
./vendor/bin/phpunit tests/Feature

# Run only Unit tests
./vendor/bin/phpunit tests/Unit

# Run specific test file
./vendor/bin/phpunit tests/Feature/AuthControllerTest.php

# Run specific test method
./vendor/bin/phpunit tests/Feature/AuthControllerTest.php --filter test_login_with_valid_credentials
```

## Coverage Configuration

Your `phpunit.xml` is already configured with:

```xml
<source>
    <include>
        <directory>app</directory>
    </include>
</source>
```

This means coverage will be generated for all files in the `app` directory.

## Coverage Thresholds

You can add coverage thresholds to your `phpunit.xml`:

```xml
<coverage>
    <report>
        <html outputDirectory="coverage/html"/>
        <clover outputFile="coverage/clover.xml"/>
    </report>
    <target>
        <directory>app</directory>
    </target>
    <threshold>
        <directory name="app" className="80" methodName="75"/>
    </threshold>
</coverage>
```

## Troubleshooting

### Common Issues

1. **"Code coverage driver not available"**
   - Install Xdebug or PCOV as shown above

2. **"Permission denied" when running scripts**
   ```bash
   chmod +x test-coverage.sh
   ```

3. **Coverage directory not found**
   ```bash
   mkdir -p coverage
   ```

### Performance Tips

- PCOV is faster than Xdebug for coverage generation
- Use `--coverage-text` for quick checks
- Use `--coverage-html` for detailed analysis
- Consider using `--filter` to run specific tests during development

## Example Workflow

```bash
# 1. Install coverage extension
brew install php@8.2-xdebug

# 2. Verify installation
php -m | grep xdebug

# 3. Run tests with coverage
composer run test-coverage

# 4. Open coverage report
open coverage/html/index.html
```

## Integration with IDEs

### VS Code
- Install "PHP Unit" extension
- Configure coverage paths in settings

### PhpStorm
- Built-in PHPUnit integration
- Coverage reports automatically integrated
