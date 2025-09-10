#!/usr/bin/env php
<?php

function cleanupFilamentFile($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Step 1: Remove duplicate imports
    $lines = explode("\n", $content);
    $imports = [];
    $newLines = [];
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Check if this is a use statement
        if (strpos($trimmedLine, 'use ') === 0 && strpos($trimmedLine, ' as ') === false) {
            // Extract the import (everything after 'use ' and before ';')
            $import = trim(str_replace(['use ', ';'], '', $trimmedLine));
            
            // Skip if we've already seen this import
            if (!in_array($import, $imports)) {
                $imports[] = $import;
                $newLines[] = $line;
            }
        } else {
            $newLines[] = $line;
        }
    }
    
    $content = implode("\n", $newLines);
    
    // Step 2: Fix remaining namespace calls that weren't caught by the first script
    $replacements = [
        // These should have been caught but let's make sure
        'Tables\\ViewAction::' => 'ViewAction::',
        'Tables\\EditAction::' => 'EditAction::',
        'Tables\\DeleteAction::' => 'DeleteAction::',
        'Tables\\Columns\\TextColumn::' => 'TextColumn::',
        'Tables\\Columns\\IconColumn::' => 'IconColumn::',
        'Tables\\Filters\\SelectFilter::' => 'SelectFilter::',
        'Tables\\Filters\\Filter::' => 'Filter::',
        'Forms\\Components\\Section::' => 'Section::',
        'Forms\\Components\\TextInput::' => 'TextInput::',
        'Forms\\Components\\Textarea::' => 'Textarea::',
        'Forms\\Components\\Select::' => 'Select::',
        'Forms\\Components\\DateTimePicker::' => 'DateTimePicker::',
        'Forms\\Components\\Toggle::' => 'Toggle::',
    ];
    
    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
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
    if (cleanupFilamentFile($file)) {
        $cleanedCount++;
        echo "Cleaned: $file\n";
    }
}

echo "\nCleaned up $cleanedCount files total.\n";
