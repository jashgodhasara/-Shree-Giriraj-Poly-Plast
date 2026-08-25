async function run() {
    const getRes = await fetch('https://shreegiriraj-erp.onrender.com/login');
    const getHtml = await getRes.text();
    const cookieHeader = getRes.headers.get('set-cookie') || '';
    const cookies = cookieHeader.split(',').map(c => c.split(';')[0]).join('; ');
    const tokenMatch = getHtml.match(/name="_token"\s+value="([^"]+)"/);
    const token = tokenMatch ? tokenMatch[1] : '';

    const postRes = await fetch('https://shreegiriraj-erp.onrender.com/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': cookies,
        },
        body: new URLSearchParams({
            '_token': token,
            'email': 'admin@shreegiriraj.com',
            'password': 'password123',
        }),
        redirect: 'manual'
    });

    const postCookies = (postRes.headers.get('set-cookie') || '').split(',').map(c => c.split(';')[0]).join('; ') || cookies;

    const empRes = await fetch('https://shreegiriraj-erp.onrender.com/employees', {
        headers: {
            'Cookie': postCookies,
            'Accept': 'text/html'
        }
    });

    console.log('HTTP Status:', empRes.status);
    const text = await empRes.text();
    console.log(text.substring(0, 2000));
}
run().catch(console.error);
