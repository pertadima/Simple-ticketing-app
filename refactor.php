#!/usr/bin/env php
<?php

function refactorFilamentFile($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Map of namespace paths to import classes
    $importMap = [
        // Actions
        'Actions\\CreateAction' => 'use Filament\\Actions\\CreateAction;',
        'Actions\\DeleteAction' => 'use Filament\\Actions\\DeleteAction;',
        'Actions\\EditAction' => 'use Filament\\Actions\\EditAction;',
        'Actions\\ViewAction' => 'use Filament\\Actions\\ViewAction;',
        
        // Tables Actions
        'Tables\\Actions\\CreateAction' => 'use Filament\\Tables\\Actions\\CreateAction;',
        'Tables\\Actions\\DeleteAction' => 'use Filament\\Tables\\Actions\\DeleteAction;',
        'Tables\\Actions\\EditAction' => 'use Filament\\Tables\\Actions\\EditAction;',
        'Tables\\Actions\\ViewAction' => 'use Filament\\Tables\\Actions\\ViewAction;',
        'Tables\\Actions\\BulkActionGroup' => 'use Filament\\Tables\\Actions\\BulkActionGroup;',
        'Tables\\Actions\\DeleteBulkAction' => 'use Filament\\Tables\\Actions\\DeleteBulkAction;',
        
        // Tables Columns
        'Tables\\Columns\\TextColumn' => 'use Filament\\Tables\\Columns\\TextColumn;',
        'Tables\\Columns\\IconColumn' => 'use Filament\\Tables\\Columns\\IconColumn;',
        
        // Tables Filters
        'Tables\\Filters\\SelectFilter' => 'use Filament\\Tables\\Filters\\SelectFilter;',
        'Tables\\Filters\\Filter' => 'use Filament\\Tables\\Filters\\Filter;',
        
        // Forms Components
        'Forms\\Components\\Section' => 'use Filament\\Forms\\Components\\Section;',
        'Forms\\Components\\TextInput' => 'use Filament\\Forms\\Components\\TextInput;',
        'Forms\\Components\\Textarea' => 'use Filament\\Forms\\Components\\Textarea;',
        'Forms\\Components\\Select' => 'use Filament\\Forms\\Components\\Select;',
        'Forms\\Components\\DateTimePicker' => 'use Filament\\Forms\\Components\\DateTimePicker;',
        'Forms\\Components\\Toggle' => 'use Filament\\Forms\\Components\\Toggle;',
        
        // Widgets
        'StatsOverviewWidget\\Stat' => 'use Filament\\Widgets\\StatsOverviewWidget\\Stat;',
    ];
    
    // Replace namespace calls with simple class names
    $replaceMap = [
        // Actions
        'Actions\\CreateAction::' => 'CreateAction::',
        'Actions\\DeleteAction::' => 'DeleteAction::',
        'Actions\\EditAction::' => 'EditAction::',
        'Actions\\ViewAction::' => 'ViewAction::',
        
        // Tables Actions  
        'Tables\\Actions\\CreateAction::' => 'CreateAction::',
        'Tables\\Actions\\DeleteAction::' => 'DeleteAction::',
        'Tables\\Actions\\EditAction::' => 'EditAction::',
        'Tables\\Actions\\ViewAction::' => 'ViewAction::',
        'Tables\\Actions\\BulkActionGroup::' => 'BulkActionGroup::',
        'Tables\\Actions\\DeleteBulkAction::' => 'DeleteBulkAction::',
        
        // Tables Columns
        'Tables\\Columns\\TextColumn::' => 'TextColumn::',
        'Tables\\Columns\\IconColumn::' => 'IconColumn::',
        
        // Tables Filters
        'Tables\\Filters\\SelectFilter::' => 'SelectFilter::',
        'Tables\\Filters\\Filter::' => 'Filter::',
        
        // Forms Components
        'Forms\\Components\\Section::' => 'Section::',
        'Forms\\Components\\TextInput::' => 'TextInput::',
        'Forms\\Components\\Textarea::' => 'Textarea::',
        'Forms\\Components\\Select::' => 'Select::',
        'Forms\\Components\\DateTimePicker::' => 'DateTimePicker::',
        'Forms\\Components\\Toggle::' => 'Toggle::',
        
        // Widgets
        'StatsOverviewWidget\\Stat::' => 'Stat::',
    ];
    
    // Find which imports are needed
    $neededImports = [];
    foreach ($importMap as $pattern => $import) {
        if (strpos($content, $pattern . '::') !== false) {
            $neededImports[] = $import;
        }
    }
    
    // Add imports after existing use statements
    if (!empty($neededImports)) {
        $lines = explode("\n", $content);
        $insertIndex = -1;
        
        // Find last use statement
        for ($i = 0; $i < count($lines); $i++) {
            if (strpos(trim($lines[$i]), 'use ') === 0) {
                $insertIndex = $i;
            }
        }
        
        if ($insertIndex >= 0) {
            // Insert new imports after the last use statement
            array_splice($lines, $insertIndex + 1, 0, $neededImports);
            $content = implode("\n", $lines);
        }
    }
    
    // Replace namespace calls with simple class names
    foreach ($replaceMap as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "Refactored: $filePath\n";
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

$refactoredCount = 0;
foreach ($files as $file) {
    if (refactorFilamentFile($file)) {
        $refactoredCount++;
    }
}

echo "\nRefactored $refactoredCount files total.\n";
