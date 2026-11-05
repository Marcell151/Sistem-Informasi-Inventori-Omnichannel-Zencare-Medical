import urllib.request
import urllib.parse
import http.cookiejar

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# Quick login as superadmin
data = urllib.parse.urlencode({'username': 'superadmin', 'password': ''}).encode('utf-8')
opener.open('http://localhost/inventory_zencare/login.php', data=data)

# Fetch master user page to see all users
res = opener.open('http://localhost/inventory_zencare/admin/master_user.php')
content = res.read().decode('utf-8', errors='ignore')

import re
users = re.findall(r'<td[^>]*class="[^"]*font-semibold[^"]*"[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>', content)
print(f"Users found on master_user.php: {len(users)}")
for u in users[:10]:
    clean_u = [re.sub(r'<[^>]+>', '', x).strip() for x in u]
    print(" -", clean_u)

