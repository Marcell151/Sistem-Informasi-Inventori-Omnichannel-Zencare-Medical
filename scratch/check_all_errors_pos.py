import subprocess
import re

for role, uid in [('superadmin', 1), ('admin', 2), ('kasir', 4)]:
    php_code = f"""
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    $_SESSION['user_id'] = {uid};
    $_SESSION['role'] = '{role}';
    $_SESSION['nama_lengkap'] = 'Test {role}';
    chdir('pos');
    ob_start();
    include 'pos.php';
    $out = ob_get_clean();
    
    // Check for PHP Warning or Notice
    preg_match_all('/(Warning|Notice|Fatal error|Parse error):[^\n<]+/i', $out, $matches);
    if (!empty($matches[0])) {{
        echo "ERRORS IN ROLE {role}:\\n";
        foreach ($matches[0] as $m) echo "  - $m\\n";
    }} else {{
        echo "ROLE {role}: Clean, no PHP notices/warnings!\\n";
    }}
    """
    res = subprocess.run([r'c:\xampp\php\php.exe', '-r', php_code], capture_output=True, text=True, cwd=r'c:\xampp\htdocs\inventory_zencare')
    print(res.stdout)

