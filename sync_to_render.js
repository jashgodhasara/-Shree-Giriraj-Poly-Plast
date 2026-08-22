const fs = require('fs');

async function syncAllToRender() {
    console.log('Logging in to Render Cloud backend...');
    const loginRes = await fetch('https://shreegiriraj-erp.onrender.com/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email: 'admin@shreegiriraj.com', password: 'Admin@1234' })
    });
    const auth = await loginRes.json();
    if (!auth.token) {
        console.error('Login failed:', auth);
        return;
    }
    console.log('✅ Render Cloud Authentication successful');

    // 1. Fetch Cloud Customers & Products mapping
    const [cloudCustRes, cloudProdRes, cloudInvRes] = await Promise.all([
        fetch('https://shreegiriraj-erp.onrender.com/api/customers', {
            headers: { Authorization: 'Bearer ' + auth.token, Accept: 'application/json' }
        }).then(r => r.json()),
        fetch('https://shreegiriraj-erp.onrender.com/api/products', {
            headers: { Authorization: 'Bearer ' + auth.token, Accept: 'application/json' }
        }).then(r => r.json()),
        fetch('https://shreegiriraj-erp.onrender.com/api/invoices', {
            headers: { Authorization: 'Bearer ' + auth.token, Accept: 'application/json' }
        }).then(r => r.json())
    ]);

    const cloudCustomers = Array.isArray(cloudCustRes?.data || cloudCustRes) ? (cloudCustRes.data || cloudCustRes) : [];
    const cloudProducts = Array.isArray(cloudProdRes?.data || cloudProdRes) ? (cloudProdRes.data || cloudProdRes) : [];
    const existingInvoices = Array.isArray(cloudInvRes?.data || cloudInvRes) ? (cloudInvRes.data || cloudInvRes) : [];

    console.log(`Cloud state: ${cloudCustomers.length} customers, ${cloudProducts.length} products, ${existingInvoices.length} existing invoices.`);

    // Map local customers to cloud customers by name
    const defaultProduct = cloudProducts[0];
    if (!defaultProduct) {
        console.error('No products found on cloud.');
        return;
    }

    // Invoices to sync
    const localInvoices = [
        {
            invoice_number: "INV-202608-0001",
            customer_name: "jash",
            date: "2026-08-17",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        },
        {
            invoice_number: "INV-202608-0002",
            customer_name: "jash",
            date: "2026-08-17",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        },
        {
            invoice_number: "INV-202608-0003",
            customer_name: "jash",
            date: "2026-08-17",
            items: [{ product_id: defaultProduct.id, quantity: 5000 }]
        },
        {
            invoice_number: "INV-202608-0004",
            customer_name: "ETERNITY POWER SOLUTIONS PRIVATE LIMITED",
            date: "2026-08-17",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        },
        {
            invoice_number: "INV-202608-0005",
            customer_name: "jash",
            date: "2026-08-18",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        },
        {
            invoice_number: "INV-202608-0006",
            customer_name: "jash",
            date: "2026-08-18",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        },
        {
            invoice_number: "INV-202608-0007",
            customer_name: "ETERNITY POWER SOLUTIONS PRIVATE LIMITED",
            date: "2026-08-20",
            items: [{ product_id: defaultProduct.id, quantity: 2 }]
        },
        {
            invoice_number: "INV-202608-0008",
            customer_name: "Dipa",
            date: "2026-08-20",
            items: [{ product_id: defaultProduct.id, quantity: 75 }]
        },
        {
            invoice_number: "INV-202608-0009",
            customer_name: "ETERNITY POWER SOLUTIONS PRIVATE LIMITED",
            date: "2026-08-20",
            items: [{ product_id: defaultProduct.id, quantity: 1 }]
        }
    ];

    for (const inv of localInvoices) {
        // Check if invoice already exists by invoice_number
        const exists = existingInvoices.find(e => e.invoice_number === inv.invoice_number);
        if (exists) {
            console.log(`Invoice ${inv.invoice_number} already on cloud, skipping.`);
            continue;
        }

        // Find customer on cloud
        let cust = cloudCustomers.find(c => c.name?.toLowerCase() === inv.customer_name.toLowerCase());
        if (!cust) {
            cust = cloudCustomers[0];
        }

        console.log(`Syncing ${inv.invoice_number} for customer ${cust.name}...`);
        const createRes = await fetch('https://shreegiriraj-erp.onrender.com/api/invoices', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + auth.token,
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify({
                customer_id: cust.id,
                items: inv.items
            })
        });
        const created = await createRes.json();
        console.log(`Result for ${inv.invoice_number}:`, created.success ? '✅ Created' : created);
    }

    console.log('🎉 Data sync to Render Cloud complete!');
}

syncAllToRender();
