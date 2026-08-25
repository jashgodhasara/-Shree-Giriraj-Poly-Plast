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
print("Found CSRF Token:", bool(token))

# 2. Login
data = urllib.parse.urlencode({
    '_token': token,
    'email': 'admin@shreegiriraj.com',
    'password': 'Admin@1234'
}).encode('utf-8')

try:
    login_res = opener.open('https://shreegiriraj-erp.onrender.com/login', data=data)
    print("Login Response URL:", login_res.geturl())
except Exception as e:
    print("Login Error:", e)

# 3. Access /employees
try:
    emp_res = opener.open('https://shreegiriraj-erp.onrender.com/employees')
    print("Employees HTTP Code:", emp_res.getcode())
    emp_html = emp_res.read().decode('utf-8')
    print("Employees HTML (First 500 chars):")
    print(emp_html[:500])
except urllib.error.HTTPError as e:
    print("Employees HTTP Error Code:", e.code)
    err_body = e.read().decode('utf-8')
    print("Error Body:")
    print(err_body[:2000])
except Exception as e:
    print("Employees Request Exception:", e)
