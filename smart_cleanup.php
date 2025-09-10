#!/usr/bin/env php
<?php

function smartCleanupUnusedImports($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Define essential imports that should never be removed
    $essentialPatterns = [
        'use Filament\\Resources\\Resource;',
        'use Filament\\Forms;',
        'use Filament\\Tables;',
        'use Filament\\Resources\\Pages\\',
        'use App\\Filament\\Resources\\.*\\Pages;',
        'use Filament\\Forms\\Form;',
        'use Filament\\Tables\\Table;',
        'use App\\Models\\',
    ];
    
    $lines = explode("\n", $content);
    $imports = [];
    $codeContent = '';
    $importLineNumbers = [];
    
    // Parse imports and code
    foreach ($lines as $lineNumber => $line) {
        $trimmed = trim($line);
        
        if (strpos($trimmed, 'use ') === 0 && strpos($trimmed, ' as ') === false) {
            preg_match('/use\s+(.+);/', $trimmed, $matches);
            if (isset($matches[1])) {
                $fullImport = $matches[1];
                $className = substr($fullImport, strrpos($fullImport, '\\') + 1);
                
                // Check if this is an essential import
                $isEssential = false;
                foreach ($essentialPatterns as $pattern) {
                    if (preg_match('/' . str_replace('\\', '\\\\', $pattern) . '/', $trimmed)) {
                        $isEssential = true;
                        break;
                    }
                }
                
                $imports[$className] = [
                    'full' => $fullImport,
                    'line' => $lineNumber,
                    'used' => $isEssential, // Mark essential imports as used
                    'essential' => $isEssential
                ];
                $importLineNumbers[] = $lineNumber;
            }
        } else {
            $codeContent .= $line . "\n";
        }
    }
    
    // Check usage of non-essential imports
    foreach ($imports as $className => $importData) {
        if (!$importData['essential'] && !$importData['used']) {
            // More comprehensive patterns for usage detection
            $patterns = [
                // Direct usage: ClassName::method()
                '/\b' . preg_quote($className, '/') . '::/m',
                // Type hints and parameters
                '/\(\s*' . preg_quote($className, '/') . '\s+\$/m',
                '/\s+' . preg_quote($className, '/') . '\s+\$/m',
                // Return types
                '/\):\s*' . preg_quote($className, '/') . '\b/m',
                // New instances
                '/new\s+' . preg_quote($className, '/') . '\s*\(/m',
                // instanceof
                '/instanceof\s+' . preg_quote($className, '/') . '\b/m',
                // In method calls
                '/\b' . preg_quote($className, '/') . '\s*\(/m',
                // As array values or in quotes
                '/[\'"]' . preg_quote($className, '/') . '[\'"]/',
                // In comments or documentation (should keep these)
                '/\*.*' . preg_quote($className, '/') . '/m',
                '//.*' . preg_quote($className, '/') . '/m',
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $codeContent)) {
                    $imports[$className]['used'] = true;
                    break;
                }
            }
        }
    }
    
    // Remove unused imports
    $removedImports = [];
    foreach ($imports as $className => $importData) {
        if (!$importData['used'] && !$importData['essential']) {
            $removedImports[] = $className;
            unset($lines[$importData['line']]);
        }
    }
    
    if (!empty($removedImports)) {
        $newContent = implode("\n", $lines);
        // Clean up multiple empty lines
        $newContent = preg_replace('/\n\n\n+/', "\n\n", $newContent);
        
        if ($newContent !== $originalContent) {
            file_put_contents($filePath, $newContent);
            return $removedImports;
        }
    }
    
    return [];
}

// Get all PHP files in Filament directory
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament'));
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$totalCleaned = 0;
$totalRemoved = 0;

echo "Running smart cleanup of unused imports...\n\n";

foreach ($files as $file) {
    $removed = smartCleanupUnusedImports($file);
    
    if (!empty($removed)) {
        $totalCleaned++;
        $totalRemoved += count($removed);
        echo "Cleaned: " . basename($file) . "\n";
        foreach ($removed as $import) {
            echo "  - Removed unused: $import\n";
        }
        echo "\n";
    }
}

echo "=== Summary ===\n";
echo "Files cleaned: $totalCleaned\n";
echo "Total unused imports removed: $totalRemoved\n";
