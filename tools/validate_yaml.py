import sys
try:
    # import yaml dynamically to avoid static analyzers complaining if PyYAML isn't installed
    import importlib
    yaml = importlib.import_module('yaml')
except Exception as e:
    print('PyYAML not installed:', e)
    print('Exit code: 2')
    sys.exit(2)

p = r'c:\Users\HASSAN\Desktop\ElitesInventoryManagement\.github\workflows\ci.yml'
try:
    with open(p, 'r', encoding='utf-8') as f:
        yaml.safe_load(f)
    print('YAML parsed OK')
    sys.exit(0)
except Exception as e:
    print('YAML parse error:')
    print(e)
    sys.exit(1)
