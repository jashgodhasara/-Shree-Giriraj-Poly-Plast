-- ERP Upgrade Script for Shree Giriraj Poly Plast

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    gstin VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Raw Material', 'Additive', 'Final Product') NOT NULL,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(10) NOT NULL, -- e.g., Kg, Pcs
    grade_variation VARCHAR(100), -- For Additives
    temp VARCHAR(50), -- For Final Product
    size VARCHAR(50), -- For Final Product
    stock_quantity DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS production_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    raw_material_id INT,
    raw_material_used_kg DECIMAL(10, 2),
    additive_id INT,
    additive_used_kg DECIMAL(10, 2),
    final_product_id INT,
    final_product_qty_pcs INT,
    salvage_qty_kg DECIMAL(10, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (raw_material_id) REFERENCES materials(id),
    FOREIGN KEY (additive_id) REFERENCES materials(id),
    FOREIGN KEY (final_product_id) REFERENCES materials(id)
);

CREATE TABLE IF NOT EXISTS transporters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    vehicle_no VARCHAR(50),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ledgers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('Customer', 'Supplier', 'Investor', 'Job Work') NOT NULL,
    entity_id INT NOT NULL,
    transaction_date DATE NOT NULL,
    type ENUM('Debit', 'Credit') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    hsn_code VARCHAR(50),
    csm_code VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
