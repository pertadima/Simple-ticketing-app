#!/bin/bash

# Script to refactor Filament PHP files to use imports instead of namespace calls

# Find all PHP files in the Filament directory
find app/Filament -name "*.php" | while read file; do
    echo "Processing: $file"
    
    # Check if file contains namespace-style method calls
    if grep -q '[A-Z][a-zA-Z]*\\[A-Z]' "$file"; then
        echo "  Found namespace calls in $file"
        
        # Extract unique namespace patterns
        grep -o '[A-Z][a-zA-Z]*\\[A-Z][a-zA-Z]*' "$file" | sort | uniq > /tmp/patterns.txt
        
        # Show what patterns were found
        echo "  Patterns found:"
        cat /tmp/patterns.txt | sed 's/^/    /'
    fi
done
