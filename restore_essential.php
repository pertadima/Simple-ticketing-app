#!/usr/bin/env php
<?php

function restoreEssentialImports($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Define essential imports based on file patterns
    $essentialImports = [];
    
    // For Resource files
    if (strpos($filePath, 'Resource.php') !== false && strpos($filePath, '/Pages/') === false) {
        $basename = basename($filePath, '.php');
        $essentialImports[] = "use App\\Filament\\Resources\\{$basename}\\Pages;";
        $essentialImports[] = "use Filament\\Resources\\Resource;";
        
        // Check if the content uses Forms or Tables namespaces
        if (strpos($content, 'Forms\\Components\\') !== false) {
            $essentialImports[] = "use Filament\\Forms;";
        }
        if (strpos($content, 'Tables\\') !== false) {
            $essentialImports[] = "use Filament\\Tables;";
        }
    }
    
    // For Page files
    if (strpos($filePath, '/Pages/') !== false) {
        if (strpos($content, 'EditRecord') !== false) {
            $essentialImports[] = "use Filament\\Resources\\Pages\\EditRecord;";
        }
        if (strpos($content, 'CreateRecord') !== false) {
            $essentialImports[] = "use Filament\\Resources\\Pages\\CreateRecord;";
        }
        if (strpos($content, 'ListRecords') !== false) {
            $essentialImports[] = "use Filament\\Resources\\Pages\\ListRecords;";
        }
        if (strpos($content, 'ViewRecord') !== false) {
            $essentialImports[] = "use Filament\\Resources\\Pages\\ViewRecord;";
        }
    }
    
    // Get existing imports
    $lines = explode("\n", $content);
    $existingImports = [];
    $lastImportLine = -1;
    
    foreach ($lines as $lineNumber => $line) {
        $trimmed = trim($line);
        if (strpos($trimmed, 'use ') === 0) {
            $existingImports[] = $trimmed;
            $lastImportLine = $lineNumber;
        }
    }
    
    // Add missing essential imports
    $newImports = [];
    foreach ($essentialImports as $import) {
        if (!in_array($import, $existingImports)) {
            $newImports[] = $import;
        }
    }
    
    if (!empty($newImports) && $lastImportLine >= 0) {
        // Insert new imports after the last existing import
        array_splice($lines, $lastImportLine + 1, 0, $newImports);
        $content = implode("\n", $lines);
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            return $newImports;
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

$restoredCount = 0;
foreach ($files as $file) {
    $restored = restoreEssentialImports($file);
    if (!empty($restored)) {
        $restoredCount++;
        echo "Restored essential imports in: " . basename($file) . "\n";
        foreach ($restored as $import) {
            echo "  + $import\n";
        }
    }
}

echo "\nRestored essential imports in $restoredCount files.\n";
