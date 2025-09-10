#!/usr/bin/env php
<?php

function removeDuplicateImports($filePath) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    $imports = [];
    $newLines = [];
    $inImportSection = false;
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Check if this is a use statement
        if (strpos($trimmedLine, 'use ') === 0 && strpos($trimmedLine, ' as ') === false) {
            $inImportSection = true;
            
            // Extract the import (everything after 'use ' and before ';')
            $import = trim(str_replace(['use ', ';'], '', $trimmedLine));
            
            // Skip if we've already seen this import
            if (!in_array($import, $imports)) {
                $imports[] = $import;
                $newLines[] = $line;
            } else {
                echo "  Removed duplicate: $import\n";
            }
        } else {
            // If we were in import section and hit a non-use line, we're done with imports
            if ($inImportSection && $trimmedLine !== '' && strpos($trimmedLine, 'use ') !== 0) {
                $inImportSection = false;
            }
            
            $newLines[] = $line;
        }
    }
    
    $newContent = implode("\n", $newLines);
    
    // Only write if content changed
    if ($content !== $newContent) {
        file_put_contents($filePath, $newContent);
        return true;
    }
    
    return false;
}

// Get all PHP files in Filament directory
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament'));
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$cleanedCount = 0;
foreach ($files as $file) {
    echo "Processing: $file\n";
    if (removeDuplicateImports($file)) {
        $cleanedCount++;
        echo "  Cleaned duplicates in: $file\n";
    }
}

echo "\nCleaned duplicate imports in $cleanedCount files total.\n";
