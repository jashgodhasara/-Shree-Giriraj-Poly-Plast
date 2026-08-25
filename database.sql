-- Complete Database Schema for Shree Giriraj Poly Plast ERP & Billing Software

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    branch_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT NULL,
    parent_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    symbol VARCHAR(20) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS unit_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_unit_id INT NOT NULL,
    to_unit_id INT NOT NULL,
    conversion_factor DECIMAL(14, 6) NOT NULL,
    operator ENUM('*', '/') DEFAULT '*',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (from_unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (to_unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    location VARCHAR(150) NULL,
    contact_person VARCHAR(150) NULL,
    contact_number VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    gstin VARCHAR(15) NULL,
    state VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    gstin VARCHAR(15) NULL,
    state VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transporters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    vehicle_number VARCHAR(50) NULL,
    address TEXT NULL,
    gstin VARCHAR(15) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'Raw Material',
    unit VARCHAR(20) DEFAULT 'KG',
    stock_quantity DECIMAL(12, 4) DEFAULT 0,
    min_stock_level DECIMAL(12, 4) DEFAULT 0,
    price_per_unit DECIMAL(10, 2) DEFAULT 0,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NULL,
    subcategory VARCHAR(150) NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    product_code VARCHAR(100) NULL,
    product_type VARCHAR(100) DEFAULT 'Finished Goods',
    material_id INT NULL,
    brand VARCHAR(150) NULL,
    unit VARCHAR(50) DEFAULT 'PCS',
    unit_type VARCHAR(50) DEFAULT 'PCS',
    purchase_unit VARCHAR(50) NULL,
    sales_unit VARCHAR(50) NULL,
    conversion_factor DECIMAL(10, 4) DEFAULT 1.0000,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    price DECIMAL(10, 2) NOT NULL,
    purchase_rate DECIMAL(10, 2) DEFAULT 0.00,
    average_cost DECIMAL(12, 4) DEFAULT 0.0000,
    sales_rate DECIMAL(10, 2) DEFAULT 0.00,
    wholesale_rate DECIMAL(10, 2) DEFAULT 0.00,
    mrp DECIMAL(10, 2) DEFAULT 0.00,
    hsn_code VARCHAR(20) NULL,
    gst_rate DECIMAL(5, 2) DEFAULT 18.00,
    barcode VARCHAR(100) NULL,
    opening_stock DECIMAL(12, 4) DEFAULT 0.0000,
    stock_quantity DECIMAL(12, 4) DEFAULT 0.0000,
    minimum_stock DECIMAL(12, 4) DEFAULT 0.0000,
    maximum_stock DECIMAL(12, 4) DEFAULT 0.0000,
    reorder_level DECIMAL(12, 4) DEFAULT 0.0000,
    weight_per_piece DECIMAL(10, 4) DEFAULT 0.0000,
    weight_unit VARCHAR(20) DEFAULT 'Gram',
    weight_in_grams DECIMAL(10, 4) DEFAULT 0.0000,
    wastage_percentage DECIMAL(5, 2) DEFAULT 2.00,
    job_work_applicable TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    warehouse_id INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stock_ledgers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NULL,
    transaction_date DATE NOT NULL,
    transaction_type VARCHAR(100) NOT NULL,
    reference_module VARCHAR(100) NULL,
    reference_id BIGINT NULL,
    reference_number VARCHAR(100) NULL,
    quantity_in DECIMAL(14, 4) DEFAULT 0.0000,
    quantity_out DECIMAL(14, 4) DEFAULT 0.0000,
    unit VARCHAR(50) DEFAULT 'PCS',
    converted_quantity DECIMAL(14, 4) DEFAULT 0.0000,
    rate DECIMAL(12, 2) DEFAULT 0.00,
    amount DECIMAL(14, 2) DEFAULT 0.00,
    previous_stock DECIMAL(14, 4) DEFAULT 0.0000,
    stock_change DECIMAL(14, 4) DEFAULT 0.0000,
    new_stock DECIMAL(14, 4) DEFAULT 0.0000,
    average_cost_after DECIMAL(14, 4) DEFAULT 0.0000,
    user_id INT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stock_adjustments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    adjustment_number VARCHAR(100) UNIQUE NOT NULL,
    adjustment_date DATE NOT NULL,
    product_id INT NOT NULL,
    warehouse_id INT NULL,
    system_stock DECIMAL(14, 4) NOT NULL,
    physical_stock DECIMAL(14, 4) NOT NULL,
    difference_quantity DECIMAL(14, 4) NOT NULL,
    adjustment_type ENUM('Increase', 'Decrease') NOT NULL,
    reason VARCHAR(255) NOT NULL,
    remarks TEXT NULL,
    status VARCHAR(50) DEFAULT 'Applied',
    created_by INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS stock_transfers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transfer_number VARCHAR(100) UNIQUE NOT NULL,
    transfer_date DATE NOT NULL,
    from_warehouse_id INT NOT NULL,
    to_warehouse_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(14, 4) NOT NULL,
    unit VARCHAR(50) DEFAULT 'PCS',
    converted_quantity DECIMAL(14, 4) DEFAULT 0.0000,
    status VARCHAR(50) DEFAULT 'Completed',
    remarks TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(150) NOT NULL,
    entity_id BIGINT NOT NULL,
    reference_number VARCHAR(100) NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    transporter_id INT NULL,
    lr_number VARCHAR(50) NULL,
    invoice_date DATE NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    cgst DECIMAL(10, 2) DEFAULT 0,
    sgst DECIMAL(10, 2) DEFAULT 0,
    igst DECIMAL(10, 2) DEFAULT 0,
    grand_total DECIMAL(10, 2) NOT NULL,
    paid_amount DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'Unpaid',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (transporter_id) REFERENCES transporters(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ledgers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    transaction_date DATE NOT NULL,
    type ENUM('Debit', 'Credit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dyes_and_moulds (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    mould_type VARCHAR(100) DEFAULT 'Injection Mould',
    cavities INT DEFAULT 1,
    ownership_type ENUM('Company Owned', 'Client Owned') DEFAULT 'Company Owned',
    customer_id INT NULL,
    product_id INT NULL,
    compatible_machines VARCHAR(255) NULL,
    rack_location VARCHAR(150) NULL,
    status VARCHAR(50) DEFAULT 'Ready / In Storage',
    total_shots_count BIGINT UNSIGNED DEFAULT 0,
    service_interval_shots BIGINT UNSIGNED DEFAULT 50000,
    last_serviced_date DATE NULL,
    next_service_due_date DATE NULL,
    purchase_cost DECIMAL(12, 2) DEFAULT 0.00,
    fabrication_date DATE NULL,
    image VARCHAR(255) NULL,
    specifications JSON NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dye_maintenance_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dye_id BIGINT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type VARCHAR(150) NOT NULL,
    shots_at_service BIGINT UNSIGNED DEFAULT 0,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    performed_by VARCHAR(150) NULL,
    vendor_name VARCHAR(150) NULL,
    work_description TEXT NULL,
    next_due_date DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (dye_id) REFERENCES dyes_and_moulds(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS factory_assets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    asset_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Moulding Machine',
    make_brand VARCHAR(150) NULL,
    model_number VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    tonnage_or_capacity VARCHAR(100) NULL,
    power_rating_kw DECIMAL(8, 2) NULL,
    plant_location VARCHAR(150) NULL,
    purchase_date DATE NULL,
    purchase_cost DECIMAL(14, 2) DEFAULT 0.00,
    warranty_expiry DATE NULL,
    supplier_id INT NULL,
    status VARCHAR(50) DEFAULT 'Operational',
    assigned_operator VARCHAR(150) NULL,
    last_service_date DATE NULL,
    next_service_date DATE NULL,
    service_interval_days INT DEFAULT 90,
    image VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS asset_maintenance_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT NOT NULL,
    service_date DATE NOT NULL,
    service_type VARCHAR(150) NOT NULL,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    technician_name VARCHAR(150) NULL,
    vendor_name VARCHAR(150) NULL,
    parts_replaced TEXT NULL,
    problem_reported TEXT NULL,
    action_taken TEXT NULL,
    status_after_service VARCHAR(50) DEFAULT 'Operational',
    next_service_due DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (asset_id) REFERENCES factory_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employees (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    designation VARCHAR(100) DEFAULT 'Machine Operator',
    department VARCHAR(100) DEFAULT 'Production',
    shift VARCHAR(100) DEFAULT 'Day Shift (8 AM - 8 PM)',
    joining_date DATE NULL,
    salary_type VARCHAR(50) DEFAULT 'Monthly',
    base_salary DECIMAL(12, 2) DEFAULT 0.00,
    overtime_hourly_rate DECIMAL(8, 2) DEFAULT 0.00,
    bank_name VARCHAR(100) NULL,
    account_number VARCHAR(50) NULL,
    ifsc_code VARCHAR(20) NULL,
    upi_id VARCHAR(100) NULL,
    aadhar_number VARCHAR(20) NULL,
    pan_number VARCHAR(20) NULL,
    status VARCHAR(50) DEFAULT 'Active',
    photo VARCHAR(255) NULL,
    address TEXT NULL,
    emergency_contact TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendances (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Present',
    in_time TIME NULL,
    out_time TIME NULL,
    working_hours DECIMAL(5, 2) DEFAULT 8.00,
    overtime_hours DECIMAL(5, 2) DEFAULT 0.00,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_employee_date (employee_id, date),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employee_advances (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT NOT NULL,
    date DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_mode VARCHAR(50) DEFAULT 'Cash',
    salary_month VARCHAR(7) NULL,
    reason VARCHAR(255) NULL,
    is_deducted TINYINT(1) DEFAULT 0,
    payroll_record_id BIGINT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_records (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    payroll_number VARCHAR(50) UNIQUE NOT NULL,
    employee_id BIGINT NOT NULL,
    month_year VARCHAR(7) NOT NULL,
    total_month_days INT DEFAULT 30,
    present_days DECIMAL(5, 2) DEFAULT 0.00,
    half_days DECIMAL(5, 2) DEFAULT 0.00,
    absent_days DECIMAL(5, 2) DEFAULT 0.00,
    paid_holidays DECIMAL(5, 2) DEFAULT 0.00,
    payable_days DECIMAL(5, 2) DEFAULT 0.00,
    base_rate DECIMAL(10, 2) DEFAULT 0.00,
    gross_salary DECIMAL(10, 2) DEFAULT 0.00,
    total_ot_hours DECIMAL(6, 2) DEFAULT 0.00,
    overtime_amount DECIMAL(10, 2) DEFAULT 0.00,
    bonus_allowances DECIMAL(10, 2) DEFAULT 0.00,
    advance_deductions DECIMAL(10, 2) DEFAULT 0.00,
    other_deductions DECIMAL(10, 2) DEFAULT 0.00,
    net_salary DECIMAL(10, 2) DEFAULT 0.00,
    paid_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_status VARCHAR(50) DEFAULT 'Unpaid',
    payment_date DATE NULL,
    payment_mode VARCHAR(50) NULL,
    transaction_reference VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_employee_payroll (employee_id, month_year),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
