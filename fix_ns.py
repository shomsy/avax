#!/usr/bin/env python3
import os
import re
from pathlib import Path

base_dir = Path("Foundation")

def fix_file(path):
    with open(path, 'r') as f:
        content = f.read()
    
    original = content
    
    # Fix namespace declarations
    content = content.replace(r"namespace Avax\HTTP\", r"namespace Avax\Foundation\HTTP\")
    content = content.replace(r"namespace Avax\Database\", r"namespace Avax\Foundation\Database\")
    content = content.replace(r"namespace Avax\DataModeling\", r"namespace Avax\Foundation\DataModeling\")
    
    # Fix use statements  
    content = content.replace(r"use Avax\HTTP\", r"use Avax\Foundation\HTTP\")
    content = content.replace(r"use Avax\Database\", r"use Avax\Foundation\Database\")
    content = content.replace(r"Avax\DataModeling\", r"Avax\Foundation\DataModeling\")
    
    if content != original:
        with open(path, 'w') as f:
            f.write(content)
        return True
    return False

count = 0
for php_file in base_dir.rglob("*.php"):
    if fix_file(php_file):
        count += 1
        print(f"Fixed: {php_file}")

print(f"Total files fixed: {count}")