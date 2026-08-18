const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const fs = require('fs');

const dbPath = path.join(__dirname, 'shreegiriraj_desktop.db');
const db = new sqlite3.Database(dbPath);

function initDb() {
    return new Promise((resolve, reject) => {
        db.serialize(() => {
            // Users table
            db.run(`CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                full_name TEXT NOT NULL,
                role TEXT DEFAULT 'operator',
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Activity Logs table
            db.run(`CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                username TEXT,
                full_name TEXT,
                action_type TEXT NOT NULL,
                module TEXT NOT NULL,
                details TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Customers table
            db.run(`CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT,
                email TEXT,
                address TEXT,
                gstin TEXT,
                state TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Suppliers table
            db.run(`CREATE TABLE IF NOT EXISTS suppliers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT,
                email TEXT,
                gstin TEXT,
                address TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Materials table (Raw Material, Additive, Final Product)
            db.run(`CREATE TABLE IF NOT EXISTS materials (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL,
                name TEXT NOT NULL,
                unit TEXT NOT NULL,
                grade_variation TEXT,
                temp TEXT,
                size TEXT,
                stock_quantity REAL DEFAULT 0,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Production Logs table
            db.run(`CREATE TABLE IF NOT EXISTS production_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date TEXT NOT NULL,
                raw_material_id INTEGER,
                raw_material_used_kg REAL,
                additive_id INTEGER,
                additive_used_kg REAL,
                final_product_id INTEGER,
                final_product_qty_pcs INTEGER,
                salvage_qty_kg REAL,
                notes TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Products table for Billing
            db.run(`CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                image TEXT,
                price REAL NOT NULL,
                hsn_code TEXT,
                gst_rate REAL DEFAULT 18.00,
                stock_quantity REAL DEFAULT 0,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Invoices table
            db.run(`CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_number TEXT UNIQUE NOT NULL,
                customer_id INTEGER NOT NULL,
                invoice_date TEXT NOT NULL,
                subtotal REAL NOT NULL,
                cgst REAL DEFAULT 0,
                sgst REAL DEFAULT 0,
                igst REAL DEFAULT 0,
                grand_total REAL NOT NULL,
                status TEXT DEFAULT 'Paid',
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Invoice Items table
            db.run(`CREATE TABLE IF NOT EXISTS invoice_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                total_price REAL NOT NULL
            )`);

            // Transporters table
            db.run(`CREATE TABLE IF NOT EXISTS transporters (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                vehicle_no TEXT,
                phone TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Ledgers table
            db.run(`CREATE TABLE IF NOT EXISTS ledgers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                entity_type TEXT NOT NULL,
                entity_id INTEGER NOT NULL,
                entity_name TEXT,
                transaction_date TEXT NOT NULL,
                type TEXT NOT NULL,
                amount REAL NOT NULL,
                hsn_code TEXT,
                csm_code TEXT,
                description TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )`);

            // Seed default 2 users if empty
            db.get("SELECT COUNT(*) as count FROM users", (err, row) => {
                if (!err && row.count === 0) {
                    const stmt = db.prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
                    stmt.run("admin", "admin123", "Shree Giriraj Admin", "admin");
                    stmt.run("operator", "operator123", "Factory Operator", "operator");
                    stmt.finalize();
                    console.log("Seeded 2 default users (admin & operator)");
                }
            });

            // Seed default materials if empty
            db.get("SELECT COUNT(*) as count FROM materials", (err, row) => {
                if (!err && row.count === 0) {
                    const stmt = db.prepare("INSERT INTO materials (type, name, unit, grade_variation, temp, size, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    stmt.run("Raw Material", "HDPE Granules Grade A", "Kg", "HDPE-100", "", "", 1500.00);
                    stmt.run("Raw Material", "LLDPE Polymer Virgin", "Kg", "LLDPE-020", "", "", 2200.00);
                    stmt.run("Additive", "Masterbatch Color Blue", "Kg", "MB-BLUE-01", "", "", 150.00);
                    stmt.run("Final Product", "Poly Pipe 75mm Heavy Duty", "Pcs", "", "120C", "75mm x 6m", 450.00);
                    stmt.run("Final Product", "Poly Sheet 200 Micron", "Pcs", "", "110C", "2m x 50m", 300.00);
                    stmt.finalize();
                    console.log("Seeded initial materials");
                }
            });

            // Seed default customers if empty
            db.get("SELECT COUNT(*) as count FROM customers", (err, row) => {
                if (!err && row.count === 0) {
                    const stmt = db.prepare("INSERT INTO customers (name, phone, email, address, gstin, state) VALUES (?, ?, ?, ?, ?, ?)");
                    stmt.run("Rajkot Poly Traders", "+91 98765 43210", "rajkotpoly@gmail.com", "Plot 12, GIDC Phase 2, Rajkot, Gujarat", "24AAAAA0000A1Z5", "Gujarat");
                    stmt.run("Gujarat Plastics Corp", "+91 98234 56789", "info@gujaratplastics.com", "Ring Road, Ahmedabad, Gujarat", "24BBBBB1111B1Z2", "Gujarat");
                    stmt.finalize();
                }
            });

            // Seed default suppliers if empty
            db.get("SELECT COUNT(*) as count FROM suppliers", (err, row) => {
                if (!err && row.count === 0) {
                    const stmt = db.prepare("INSERT INTO suppliers (name, phone, email, gstin, address) VALUES (?, ?, ?, ?, ?)");
                    stmt.run("Reliance Polymers Pvt Ltd", "+91 22 2888 9999", "sales@reliancepolymers.com", "24CCCC2222C1Z8", "Hazira Complex, Surat, Gujarat");
                    stmt.run("Supreme Petrochem Ltd", "+91 22 4000 1111", "contact@supremepetro.com", "24DDDD3333D1Z4", "Vadodara GIDC, Gujarat");
                    stmt.finalize();
                }
            });

            // Seed default products for billing if empty
            db.get("SELECT COUNT(*) as count FROM products", (err, row) => {
                if (!err && row.count === 0) {
                    const stmt = db.prepare("INSERT INTO products (name, description, price, hsn_code, gst_rate, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
                    stmt.run("Poly Pipe 75mm Heavy Duty", "High-density poly pipe for agriculture and industry", 850.00, "391721", 18.00, 450);
                    stmt.run("Poly Sheet 200 Micron", "Heavy duty protective poly sheet roll", 1250.00, "392010", 18.00, 300);
                    stmt.run("Poly Fitting Socket 75mm", "Durable poly connector socket", 120.00, "391740", 18.00, 1000);
                    stmt.finalize();
                    resolve();
                } else {
                    resolve();
                }
            });
        });
    });
}

function logActivity(userId, username, fullName, actionType, module, details) {
    db.run(
        "INSERT INTO activity_logs (user_id, username, full_name, action_type, module, details) VALUES (?, ?, ?, ?, ?, ?)",
        [userId, username, fullName, actionType, module, details]
    );
}

module.exports = {
    db,
    initDb,
    logActivity
};
