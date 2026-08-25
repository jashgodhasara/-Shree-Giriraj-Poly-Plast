const https = require('https');

async function testRender() {
    console.log('Testing https://shreegiriraj-erp.onrender.com/login...');

    // 1. Get Login Page & CSRF Token
    const getRes = await fetch('https://shreegiriraj-erp.onrender.com/login');
    const getHtml = await getRes.text();
    const cookies = getRes.headers.get('set-cookie');
    
    const tokenMatch = getHtml.match(/name="_token"\s+value="([^"]+)"/);
    const token = tokenMatch ? tokenMatch[1] : '';
    console.log('CSRF Token found:', !!token);

    // 2. Post Login
    const postRes = await fetch('https://shreegiriraj-erp.onrender.com/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': cookies || '',
        },
        body: new URLSearchParams({
            '_token': token,
            'email': 'admin@shreegiriraj.com',
            'password': 'password123',
        }),
        redirect: 'manual'
    });

    const loginCookies = postRes.headers.get('set-cookie') || cookies;
    console.log('Login response status:', postRes.status);
    console.log('Login location header:', postRes.headers.get('location'));

    // 3. Request /employees
    const empRes = await fetch('https://shreegiriraj-erp.onrender.com/employees', {
        headers: {
            'Cookie': loginCookies,
            'Accept': 'text/html'
        }
    });

    console.log('/employees HTTP status:', empRes.status);
    const empText = await empRes.text();
    console.log('/employees response preview (first 500 chars):\n', empText.substring(0, 500));
}

testRender().catch(err => console.error('Error:', err));
