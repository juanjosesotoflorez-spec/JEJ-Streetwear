import os
import zipfile

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ZIP_PATH = os.path.join(BASE_DIR, 'jej-streetwear_listo_para_subir.zip')

EXCLUDE_DIRS = {'.git', '.vscode', '.gemini', 'node_modules'}
EXCLUDE_FILES = {'jej-streetwear_listo_para_subir.zip', 'cloudflared.exe'}
EXCLUDE_EXTS = {'.log'}

with zipfile.ZipFile(ZIP_PATH, 'w', zipfile.ZIP_DEFLATED) as z:
    for root, dirs, files in os.walk(BASE_DIR):
        # Exclude directories
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS and not d.startswith('.')]
        
        for file in files:
            if file in EXCLUDE_FILES or any(file.endswith(ext) for ext in EXCLUDE_EXTS):
                continue
            
            # Skip test scripts created in scripts folder, keeping create_admin.php
            if file.startswith('test_') or file in ['trigger_setup.py', 'inspect_error.py', 'inspect_nike_categories.py', 'extract_all_nike.py', 'verify_live_catalog.py', 'check_distinct.py']:
                continue
            
            full_path = os.path.join(root, file)
            rel_path = os.path.relpath(full_path, BASE_DIR).replace('\\', '/')
            z.write(full_path, rel_path)

print(f"Updated {ZIP_PATH} successfully! Size: {os.path.getsize(ZIP_PATH)} bytes")

