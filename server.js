const express = require('express');
const cors = require('cors');
const path = require('path');
const { db, initDb, logActivity } = require('./desktop_db');

const app = express();
const PORT = process.env.PORT || 4000;

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));

// Helper promises for sqlite
const dbAll = (sql, params = []) => new Promise((resolve, reject) => {
    db.all(sql, params, (err, rows) => err ? reject(err) : resolve(rows));
});
const dbGet = (sql, params = []) => new Promise((resolve, reject) => {
    db.get(sql, params, (err, row) => err ? reject(err) : resolve(row));
});
const dbRun = (sql, params = []) => new Promise((resolve, reject) => {
    db.run(sql, params, function(err) { err ? reject(err) : resolve(this); });
});

// AUTH ENDPOINTS
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    try {
        const user = await dbGet("SELECT id, username, full_name, role, status FROM users WHERE username = ? AND password = ?", [username, password]);
        if (!user) {
            return res.status(401).json({ success: false, message: 'Invalid username or password' });
        }
        if (user.status !== 'active') {
            return res.status(403).json({ success: false, message: 'Account is deactivated' });
        }
        logActivity(user.id, user.username, user.full_name, 'LOGIN', 'Auth', `Logged into desktop system`);
        res.json({ success: true, user });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// DASHBOARD STATS
app.get('/api/stats', async (req, res) => {
    try {
        const custCount = (await dbGet("SELECT COUNT(*) as cnt FROM customers")).cnt;
        const suppCount = (await dbGet("SELECT COUNT(*) as cnt FROM suppliers")).cnt;
        const rawStock = (await dbGet("SELECT SUM(stock_quantity) as total FROM materials WHERE type='Raw Material'")).total || 0;
        const finalStock = (await dbGet("SELECT SUM(stock_quantity) as total FROM materials WHERE type='Final Product'")).total || 0;
        const recentProd = await dbAll(`
            SELECT p.*, rm.name as rm_name, fp.name as fp_name 
            FROM production_logs p
            LEFT JOIN materials rm ON p.raw_material_id = rm.id
            LEFT JOIN materials fp ON p.final_product_id = fp.id
            ORDER BY p.id DESC LIMIT 5
        `);
        const recentInvoices = await dbAll(`
            SELECT i.*, c.name as customer_name 
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            ORDER BY i.id DESC LIMIT 5
        `);
        res.json({
            custCount,
            suppCount,
            rawStock,
            finalStock,
            recentProd,
            recentInvoices
        });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// MATERIALS API
app.get('/api/materials', async (req, res) => {
    try {
        const materials = await dbAll("SELECT * FROM materials ORDER BY id DESC");
        res.json(materials);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/materials', async (req, res) => {
    const { type, name, unit, grade_variation, temp, size, stock_quantity, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO materials (type, name, unit, grade_variation, temp, size, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [type, name, unit, grade_variation || '', temp || '', size || '', stock_quantity || 0]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Materials', `Added new material: ${name} (${type})`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// PRODUCTION LOGS API
app.get('/api/production', async (req, res) => {
    try {
        const logs = await dbAll(`
            SELECT p.*, rm.name as rm_name, add_mat.name as additive_name, fp.name as fp_name 
            FROM production_logs p
            LEFT JOIN materials rm ON p.raw_material_id = rm.id
            LEFT JOIN materials add_mat ON p.additive_id = add_mat.id
            LEFT JOIN materials fp ON p.final_product_id = fp.id
            ORDER BY p.id DESC
        `);
        res.json(logs);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/production', async (req, res) => {
    const {
        date, raw_material_id, raw_material_used_kg,
        additive_id, additive_used_kg,
        final_product_id, final_product_qty_pcs,
        salvage_qty_kg, notes, user_id, username, full_name
    } = req.body;

    try {
        // Record log
        const result = await dbRun(
            `INSERT INTO production_logs 
            (date, raw_material_id, raw_material_used_kg, additive_id, additive_used_kg, final_product_id, final_product_qty_pcs, salvage_qty_kg, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [date, raw_material_id, raw_material_used_kg, additive_id || null, additive_used_kg || 0, final_product_id, final_product_qty_pcs, salvage_qty_kg || 0, notes || '', user_id]
        );

        // Deduct raw material stock
        if (raw_material_id && raw_material_used_kg > 0) {
            await dbRun("UPDATE materials SET stock_quantity = stock_quantity - ? WHERE id = ?", [raw_material_used_kg, raw_material_id]);
        }

        // Deduct additive stock
        if (additive_id && additive_used_kg > 0) {
            await dbRun("UPDATE materials SET stock_quantity = stock_quantity - ? WHERE id = ?", [additive_used_kg, additive_id]);
        }

        // Add final product stock
        if (final_product_id && final_product_qty_pcs > 0) {
            await dbRun("UPDATE materials SET stock_quantity = stock_quantity + ? WHERE id = ?", [final_product_qty_pcs, final_product_id]);
        }

        logActivity(user_id, username, full_name, 'CREATE', 'Production', `Logged production run of ${final_product_qty_pcs} Pcs (Log #${result.lastID})`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// CUSTOMERS API
app.get('/api/customers', async (req, res) => {
    try {
        const customers = await dbAll("SELECT * FROM customers ORDER BY name ASC");
        res.json(customers);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/customers', async (req, res) => {
    const { name, phone, email, address, gstin, state, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO customers (name, phone, email, address, gstin, state, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [name, phone, email, address, gstin, state || 'Gujarat', user_id]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Customers', `Created customer: ${name}`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// SUPPLIERS API
app.get('/api/suppliers', async (req, res) => {
    try {
        const suppliers = await dbAll("SELECT * FROM suppliers ORDER BY name ASC");
        res.json(suppliers);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/suppliers', async (req, res) => {
    const { name, phone, email, gstin, address, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO suppliers (name, phone, email, gstin, address, created_by) VALUES (?, ?, ?, ?, ?, ?)",
            [name, phone, email, gstin, address, user_id]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Suppliers', `Created supplier: ${name}`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// PRODUCTS API FOR BILLING
app.get('/api/products', async (req, res) => {
    try {
        const products = await dbAll("SELECT * FROM products ORDER BY name ASC");
        res.json(products);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/products', async (req, res) => {
    const { name, description, price, hsn_code, gst_rate, stock_quantity, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO products (name, description, price, hsn_code, gst_rate, stock_quantity, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [name, description, price, hsn_code, gst_rate || 18.00, stock_quantity || 0, user_id]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Products', `Created billing product: ${name}`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// INVOICES API
app.get('/api/invoices', async (req, res) => {
    try {
        const invoices = await dbAll(`
            SELECT i.*, c.name as customer_name, c.gstin as customer_gstin, c.phone as customer_phone
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            ORDER BY i.id DESC
        `);
        res.json(invoices);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.get('/api/invoices/:id', async (req, res) => {
    try {
        const invoice = await dbGet(`
            SELECT i.*, c.name as customer_name, c.gstin as customer_gstin, c.phone as customer_phone, c.address as customer_address
            FROM invoices i
            JOIN customers c ON i.customer_id = c.id
            WHERE i.id = ?
        `, [req.params.id]);

        if (!invoice) return res.status(404).json({ success: false, message: 'Invoice not found' });

        const items = await dbAll("SELECT * FROM invoice_items WHERE invoice_id = ?", [req.params.id]);
        res.json({ invoice, items });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/invoices', async (req, res) => {
    const { customer_id, invoice_date, items, is_interstate, user_id, username, full_name } = req.body;
    try {
        // Generate Invoice Number SGP-YYYY-00X
        const countRow = await dbGet("SELECT COUNT(*) as count FROM invoices");
        const invNum = `SGP-${new Date().getFullYear()}-${String(countRow.count + 1).padStart(4, '0')}`;

        let subtotal = 0;
        items.forEach(item => {
            subtotal += item.quantity * item.unit_price;
        });

        let cgst = 0, sgst = 0, igst = 0;
        if (is_interstate) {
            igst = subtotal * 0.18;
        } else {
            cgst = subtotal * 0.09;
            sgst = subtotal * 0.09;
        }

        const grand_total = subtotal + cgst + sgst + igst;

        const result = await dbRun(
            "INSERT INTO invoices (invoice_number, customer_id, invoice_date, subtotal, cgst, sgst, igst, grand_total, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [invNum, customer_id, invoice_date, subtotal, cgst, sgst, igst, grand_total, user_id]
        );

        const invoiceId = result.lastID;

        // Insert items & deduct product stock
        for (let item of items) {
            const totalPrice = item.quantity * item.unit_price;
            await dbRun(
                "INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)",
                [invoiceId, item.product_id, item.product_name, item.quantity, item.unit_price, totalPrice]
            );
            await dbRun("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?", [item.quantity, item.product_id]);
        }

        logActivity(user_id, username, full_name, 'CREATE', 'Invoices', `Generated Invoice ${invNum} for ₹${grand_total.toFixed(2)}`);
        res.json({ success: true, invoice_id: invoiceId, invoice_number: invNum });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// LEDGERS API
app.get('/api/ledgers', async (req, res) => {
    try {
        const ledgers = await dbAll("SELECT * FROM ledgers ORDER BY id DESC");
        res.json(ledgers);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/ledgers', async (req, res) => {
    const { entity_type, entity_id, entity_name, transaction_date, type, amount, hsn_code, description, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO ledgers (entity_type, entity_id, entity_name, transaction_date, type, amount, hsn_code, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [entity_type, entity_id, entity_name, transaction_date, type, amount, hsn_code || '', description || '', user_id]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Ledger', `Recorded ${type} transaction of ₹${amount} for ${entity_name}`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// TRANSPORTERS API
app.get('/api/transporters', async (req, res) => {
    try {
        const transporters = await dbAll("SELECT * FROM transporters ORDER BY id DESC");
        res.json(transporters);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/api/transporters', async (req, res) => {
    const { name, vehicle_no, phone, user_id, username, full_name } = req.body;
    try {
        const result = await dbRun(
            "INSERT INTO transporters (name, vehicle_no, phone, created_by) VALUES (?, ?, ?, ?)",
            [name, vehicle_no, phone, user_id]
        );
        logActivity(user_id, username, full_name, 'CREATE', 'Transporters', `Added transporter: ${name} (${vehicle_no})`);
        res.json({ success: true, id: result.lastID });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// ACTIVITY LOGS API
app.get('/api/logs', async (req, res) => {
    try {
        const logs = await dbAll("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 100");
        res.json(logs);
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Serve main desktop html
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'desktop_app.html'));
});

app.use((req, res) => {
    res.sendFile(path.join(__dirname, 'desktop_app.html'));
});

// Start Server
initDb().then(() => {
    app.listen(PORT, () => {
        console.log(`Shree Giriraj Poly Plast Desktop Server running on http://localhost:${PORT}`);
    });
}).catch(err => {
    console.error("Database initialization failed:", err);
});
