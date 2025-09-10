#!/usr/bin/env php
<?php

function removeUnusedImports($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Parse the file to get imports and code content
    $lines = explode("\n", $content);
    $imports = [];
    $importLines = [];
    $codeContent = '';
    $inImportSection = false;
    
    foreach ($lines as $lineNumber => $line) {
        $trimmed = trim($line);
        
        if (strpos($trimmed, 'use ') === 0 && strpos($trimmed, ' as ') === false) {
            // Extract class name from import
            preg_match('/use\s+(.+);/', $trimmed, $matches);
            if (isset($matches[1])) {
                $fullImport = $matches[1];
                $className = substr($fullImport, strrpos($fullImport, '\\') + 1);
                $imports[$className] = [
                    'full' => $fullImport,
                    'line' => $lineNumber,
                    'used' => false
                ];
                $importLines[] = $lineNumber;
            }
            $inImportSection = true;
        } elseif ($inImportSection && $trimmed !== '' && strpos($trimmed, 'use ') !== 0) {
            $inImportSection = false;
        }
        
        // Collect non-import content for analysis
        if (!$inImportSection && strpos($trimmed, 'use ') !== 0) {
            $codeContent .= $line . "\n";
        }
    }
    
    // Check which imports are actually used
    foreach ($imports as $className => $importData) {
        // Check if class name is used in the code (not in comments or strings)
        $patterns = [
            // Direct class usage: ClassName::method()
            '/\b' . preg_quote($className, '/') . '::/m',
            // Type hints: function(ClassName $param)
            '/\(\s*' . preg_quote($className, '/') . '\s+\$/m',
            // Return types: ): ClassName
            '/\):\s*' . preg_quote($className, '/') . '\b/m',
            // Property types: ClassName $property
            '/\b' . preg_quote($className, '/') . '\s+\$/m',
            // New instances: new ClassName()
            '/new\s+' . preg_quote($className, '/') . '\s*\(/m',
            // instanceof checks
            '/instanceof\s+' . preg_quote($className, '/') . '\b/m',
            // In arrays or method calls
            '/[\'"]' . preg_quote($className, '/') . '[\'"]/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $codeContent)) {
                $imports[$className]['used'] = true;
                break;
            }
        }
    }
    
    // Remove unused import lines
    $unusedImports = [];
    foreach ($imports as $className => $importData) {
        if (!$importData['used']) {
            $unusedImports[] = $className;
            unset($lines[$importData['line']]);
        }
    }
    
    if (!empty($unusedImports)) {
        // Rebuild content without unused imports
        $newContent = implode("\n", $lines);
        
        // Clean up any double empty lines that might have been created
        $newContent = preg_replace('/\n\n\n+/', "\n\n", $newContent);
        
        if ($newContent !== $originalContent) {
            file_put_contents($filePath, $newContent);
            return $unusedImports;
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
$totalUnusedRemoved = 0;

foreach ($files as $file) {
    echo "Analyzing: " . basename($file) . "\n";
    $unusedImports = removeUnusedImports($file);
    
    if (!empty($unusedImports)) {
        $totalCleaned++;
        $totalUnusedRemoved += count($unusedImports);
        echo "  Removed unused imports:\n";
        foreach ($unusedImports as $unused) {
            echo "    - $unused\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Files cleaned: $totalCleaned\n";
echo "Total unused imports removed: $totalUnusedRemoved\n";
