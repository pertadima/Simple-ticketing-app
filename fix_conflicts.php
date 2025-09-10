#!/usr/bin/env php
<?php

function fixImportConflicts($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $lines = explode("\n", $content);
    
    $cleanedLines = [];
    $importsSeen = [];
    
    // Step 1: Clean duplicate imports and prioritize Tables\Actions over Actions
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        if (strpos($trimmed, 'use Filament\\Actions\\') === 0) {
            // Check if we have a corresponding Tables\Actions version
            $className = substr($trimmed, strrpos($trimmed, '\\') + 1, -1); // Remove semicolon
            $tablesVersion = str_replace('use Filament\\Actions\\', 'use Filament\\Tables\\Actions\\', $trimmed);
            
            // Skip the Filament\Actions version if Tables\Actions version exists in file
            if (strpos($content, $tablesVersion) !== false) {
                continue; // Skip this import
            }
        }
        
        // For all use statements, check for duplicates
        if (strpos($trimmed, 'use ') === 0) {
            $import = trim(str_replace(['use ', ';'], '', $trimmed));
            if (!in_array($import, $importsSeen)) {
                $importsSeen[] = $import;
                $cleanedLines[] = $line;
            }
        } else {
            $cleanedLines[] = $line;
        }
    }
    
    $content = implode("\n", $cleanedLines);
    
    // Step 2: Fix remaining namespace calls
    $replacements = [
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

$fixedCount = 0;
foreach ($files as $file) {
    if (fixImportConflicts($file)) {
        $fixedCount++;
        echo "Fixed conflicts in: $file\n";
    }
}

echo "\nFixed import conflicts in $fixedCount files total.\n";
