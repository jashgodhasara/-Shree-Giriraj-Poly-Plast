import http.cookiejar
import urllib.request
import urllib.parse
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Get Login Page
res = opener.open('https://shreegiriraj-erp.onrender.com/login')
html = res.read().decode('utf-8')
token_match = re.search(r'name="_token"\s+value="([^"]+)"', html)
token = token_match.group(1) if token_match else ''

# 2. Login
data = urllib.parse.urlencode({
    '_token': token,
    'email': 'admin@shreegiriraj.com',
    'password': 'Admin@1234'
}).encode('utf-8')

opener.open('https://shreegiriraj-erp.onrender.com/login', data=data)

urls = [
    '/employees',
    '/attendance',
    '/attendance/monthly',
    '/employee-advances',
    '/payroll',
    '/dyes',
    '/factory-assets',
    '/products',
    '/inventory/dashboard',
    '/inventory/warehouses',
    '/api/staff/employees',
    '/api/staff/attendance/today',
    '/api/staff/advances',
    '/api/staff/payroll',
]

for u in urls:
    full_url = 'https://shreegiriraj-erp.onrender.com' + u
    try:
        r = opener.open(full_url)
        print(f"[OK] {u}: HTTP {r.getcode()}")
    except urllib.error.HTTPError as e:
        print(f"[FAIL] {u}: HTTP {e.code} - {e.reason}")
        body = e.read().decode('utf-8')
        print("   Details:", body[:300])
    except Exception as e:
        print(f"[ERROR] {u}: Exception {e}")
