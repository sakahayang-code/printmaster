-- PRINTMASTER SQLITE DATABASE SCHEMA (62 TABEL)
-- Generated: 2026-11-13 11:54:57

PRAGMA foreign_keys = OFF;

CREATE TABLE accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(120) NOT NULL,
    type TEXT NOT NULL,
    normal_balance TEXT NOT NULL,
    parent_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES accounts(id) ON DELETE SET NULL
);

CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NULL,
    branch_id INT NULL,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id INT NULL,
    old_values LONGTEXT NULL,
    new_values LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE backup_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename VARCHAR(255) NOT NULL,
    file_size BIGINT DEFAULT 0,
    backup_type TEXT DEFAULT 'database',
    status TEXT DEFAULT 'success',
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE branch_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    branch_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (branch_id, setting_key),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

CREATE TABLE branches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(150) NOT NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    is_headquarters TINYINT(1) DEFAULT 0,
    operating_hours VARCHAR(100) DEFAULT '08:00 - 21:00',
    monthly_target DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE cash_registers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

CREATE TABLE cash_shifts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    branch_id INT NOT NULL,
    cash_register_id INT NOT NULL,
    user_id INT NOT NULL,
    opening_time DATETIME NOT NULL,
    closing_time DATETIME NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    cash_sales DECIMAL(15,2) DEFAULT 0.00,
    cash_refunds DECIMAL(15,2) DEFAULT 0.00,
    expected_balance DECIMAL(15,2) DEFAULT 0.00,
    actual_balance DECIMAL(15,2) DEFAULT 0.00,
    difference DECIMAL(15,2) DEFAULT 0.00,
    status TEXT DEFAULT 'open',
    notes TEXT NULL,
    approved_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    legal_name VARCHAR(200) NULL,
    tax_number VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    logo VARCHAR(255) NULL,
    currency VARCHAR(10) DEFAULT 'IDR',
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP 
);

CREATE TABLE company_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, setting_key),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE customer_addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INT NOT NULL,
    label VARCHAR(50) DEFAULT 'Utama',
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE customer_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL DEFAULT 5,
    comments TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NULL,
    customer_code VARCHAR(50) NOT NULL,
    type TEXT DEFAULT 'individual',
    name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NULL,
    email VARCHAR(120) NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NULL,
    tax_id VARCHAR(50) NULL,
    customer_tier TEXT DEFAULT 'regular',
    discount_percent DECIMAL(5,2) DEFAULT 0.00,
    credit_limit DECIMAL(15,2) DEFAULT 0.00,
    current_receivable DECIMAL(15,2) DEFAULT 0.00,
    loyalty_points INT DEFAULT 0,
    password_hash VARCHAR(255) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, customer_code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

CREATE TABLE delivery_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    order_id INT NOT NULL,
    delivery_number VARCHAR(50) NOT NULL UNIQUE,
    delivery_type TEXT DEFAULT 'customer_delivery',
    courier_type TEXT DEFAULT 'internal',
    courier_name VARCHAR(100) NOT NULL,
    tracking_number VARCHAR(100) NULL,
    recipient_name VARCHAR(100) NOT NULL,
    recipient_phone VARCHAR(50) NOT NULL,
    delivery_address TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    estimated_delivery_time DATETIME NULL,
    actual_delivery_time DATETIME NULL,
    proof_image_path VARCHAR(255) NULL,
    recipient_signature TEXT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE design_revisions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INT NOT NULL,
    version_number INT DEFAULT 1,
    proof_image_path VARCHAR(255) NOT NULL,
    designer_id INT NOT NULL,
    notes TEXT NULL,
    status TEXT DEFAULT 'draft',
    customer_feedback TEXT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (designer_id) REFERENCES users(id)
);

CREATE TABLE employee_attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INT NOT NULL,
    branch_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    status TEXT DEFAULT 'present',
    location_lat DECIMAL(10,8) NULL,
    location_lng DECIMAL(11,8) NULL,
    notes VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (employee_id, date),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

CREATE TABLE employee_shifts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    late_tolerance_minutes INT DEFAULT 15,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    user_id INT NULL UNIQUE,
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    id_number VARCHAR(50) NULL,
    gender TEXT DEFAULT 'M',
    birth_date DATE NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(120) NULL,
    address TEXT NULL,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    join_date DATE NOT NULL,
    basic_salary DECIMAL(15,2) DEFAULT 0.00,
    allowance_fixed DECIMAL(15,2) DEFAULT 0.00,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    expense_number VARCHAR(50) NOT NULL UNIQUE,
    expense_date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method TEXT DEFAULT 'cash',
    account_id INT NULL,
    paid_to VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    receipt_file VARCHAR(255) NULL,
    approved_by INT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
);

CREATE TABLE goods_receipt_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    goods_receipt_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity_received DECIMAL(12,4) NOT NULL,
    unit VARCHAR(30) NOT NULL,
    notes VARCHAR(255) NULL,
    FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE goods_receipts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_order_id INT NOT NULL,
    branch_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    receipt_date DATE NOT NULL,
    delivery_note_number VARCHAR(80) NULL,
    received_by INT NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE journal_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    journal_id INT NOT NULL,
    account_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0.00,
    credit DECIMAL(15,2) DEFAULT 0.00,
    memo VARCHAR(255) NULL,
    FOREIGN KEY (journal_id) REFERENCES journals(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);

CREATE TABLE journals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    journal_number VARCHAR(50) NOT NULL UNIQUE,
    journal_date DATE NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id INT NULL,
    description TEXT NOT NULL,
    total_debit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_credit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status TEXT DEFAULT 'posted',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

CREATE TABLE leave_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INT NOT NULL,
    leave_type TEXT DEFAULT 'annual',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL DEFAULT 1,
    reason TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    approved_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100) NOT NULL,
    is_success TINYINT(1) DEFAULT 0,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE machine_maintenance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    machine_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type TEXT DEFAULT 'routine',
    cost DECIMAL(15,2) DEFAULT 0.00,
    description TEXT NOT NULL,
    performed_by VARCHAR(100) NOT NULL,
    next_schedule DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE
);

CREATE TABLE machines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    brand_model VARCHAR(100) NULL,
    machine_type TEXT DEFAULT 'outdoor_flexy',
    capacity_per_hour DECIMAL(10,2) DEFAULT 0.00,
    hourly_cost DECIMAL(15,2) DEFAULT 0.00,
    meter_counter DECIMAL(15,2) DEFAULT 0.00,
    status TEXT DEFAULT 'available',
    last_maintenance_date DATE NULL,
    notes TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (branch_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

CREATE TABLE materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    category TEXT DEFAULT 'paper',
    unit VARCHAR(30) DEFAULT 'pcs',
    min_stock DECIMAL(12,2) DEFAULT 10.00,
    current_stock DECIMAL(12,2) DEFAULT 0.00,
    avg_cost DECIMAL(15,2) DEFAULT 0.00,
    last_purchase_price DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE notification_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NULL,
    recipient VARCHAR(100) NOT NULL,
    channel TEXT DEFAULT 'whatsapp',
    subject VARCHAR(150) NULL,
    message_content TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    response_log TEXT NULL,
    sent_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE notification_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    event_code VARCHAR(80) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message_template TEXT NOT NULL,
    channel TEXT DEFAULT 'whatsapp',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (company_id, event_code, channel),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE order_files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INT NOT NULL,
    order_item_id INT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    resolution_dpi INT NULL,
    color_mode VARCHAR(30) NULL,
    remarks TEXT NULL,
    uploaded_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    specs_json TEXT NULL,
    width_cm DECIMAL(10,2) DEFAULT 0.00,
    height_cm DECIMAL(10,2) DEFAULT 0.00,
    area_sqm DECIMAL(10,4) DEFAULT 0.0000,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit VARCHAR(30) DEFAULT 'pcs',
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cost_estimate DECIMAL(15,2) DEFAULT 0.00,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    tax DECIMAL(15,2) DEFAULT 0.00,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    finishing_details TEXT NULL,
    price_snapshot_json LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE order_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    quote_id INT NULL,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    deadline_date DATETIME NOT NULL,
    pickup_delivery_type TEXT DEFAULT 'pickup',
    shipping_address TEXT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    voucher_code VARCHAR(50) NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    remaining_amount DECIMAL(15,2) DEFAULT 0.00,
    payment_status TEXT DEFAULT 'unpaid',
    status TEXT DEFAULT 'pending_confirmation',
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    order_id INT NULL,
    purchase_order_id INT NULL,
    payment_number VARCHAR(50) NOT NULL UNIQUE,
    payment_type TEXT DEFAULT 'order_payment',
    payment_method TEXT DEFAULT 'cash',
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATETIME NOT NULL,
    reference_number VARCHAR(100) NULL,
    proof_file_path VARCHAR(255) NULL,
    verified_by INT NULL,
    status TEXT DEFAULT 'verified',
    idempotency_key VARCHAR(100) NULL UNIQUE,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL
);

CREATE TABLE payrolls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    period_month INT NOT NULL,
    period_year INT NOT NULL,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(15,2) NOT NULL,
    allowances DECIMAL(15,2) DEFAULT 0.00,
    overtime_pay DECIMAL(15,2) DEFAULT 0.00,
    bonus DECIMAL(15,2) DEFAULT 0.00,
    deductions DECIMAL(15,2) DEFAULT 0.00,
    net_salary DECIMAL(15,2) NOT NULL,
    payment_status TEXT DEFAULT 'draft',
    paid_at DATETIME NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (employee_id, period_month, period_year),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(80) DEFAULT 'bi-printer',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE product_finishing (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    calculation_type TEXT DEFAULT 'per_pcs',
    cost_price DECIMAL(15,2) DEFAULT 0.00,
    selling_price DECIMAL(15,2) DEFAULT 0.00,
    min_fee DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE product_materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INT NOT NULL,
    material_id INT NOT NULL,
    default_usage_qty DECIMAL(10,4) DEFAULT 1.0000,
    is_required TINYINT(1) DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

CREATE TABLE product_variants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) NULL,
    additional_price DECIMAL(15,2) DEFAULT 0.00,
    specs_json TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE production_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    source_branch_id INT NOT NULL,
    target_branch_id INT NOT NULL,
    order_id INT NOT NULL,
    order_item_id INT NOT NULL,
    job_number VARCHAR(50) NOT NULL UNIQUE,
    machine_id INT NULL,
    operator_id INT NULL,
    priority TEXT DEFAULT 'normal',
    status TEXT DEFAULT 'queued',
    estimated_duration_minutes INT DEFAULT 60,
    actual_duration_minutes INT DEFAULT 0,
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    scheduled_date DATE NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (source_branch_id) REFERENCES branches(id),
    FOREIGN KEY (target_branch_id) REFERENCES branches(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE SET NULL,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE production_materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    production_job_id INT NOT NULL,
    material_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    planned_quantity DECIMAL(12,4) NOT NULL,
    actual_quantity DECIMAL(12,4) DEFAULT 0.0000,
    scrap_quantity DECIMAL(12,4) DEFAULT 0.0000,
    unit_cost DECIMAL(15,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (production_job_id) REFERENCES production_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE production_reprints (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    original_job_id INT NOT NULL,
    new_job_id INT NULL,
    reason TEXT NOT NULL,
    defect_category VARCHAR(100) NOT NULL,
    responsible_role VARCHAR(80) NOT NULL,
    cost_impact DECIMAL(15,2) DEFAULT 0.00,
    approved_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (original_job_id) REFERENCES production_jobs(id),
    FOREIGN KEY (new_job_id) REFERENCES production_jobs(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE production_steps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    production_job_id INT NOT NULL,
    step_name TEXT NOT NULL,
    sequence INT DEFAULT 1,
    status TEXT DEFAULT 'pending',
    operator_id INT NULL,
    notes TEXT NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    FOREIGN KEY (production_job_id) REFERENCES production_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    category_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    pricing_mode TEXT DEFAULT 'pcs',
    base_price DECIMAL(15,2) DEFAULT 0.00,
    min_price DECIMAL(15,2) DEFAULT 0.00,
    min_order_qty INT DEFAULT 1,
    default_margin_percent DECIMAL(5,2) DEFAULT 30.00,
    waste_percent DECIMAL(5,2) DEFAULT 5.00,
    setup_cost DECIMAL(15,2) DEFAULT 0.00,
    overhead_cost DECIMAL(15,2) DEFAULT 0.00,
    image VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

CREATE TABLE promotions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    discount_type TEXT DEFAULT 'percentage',
    discount_value DECIMAL(15,2) NOT NULL,
    min_purchase DECIMAL(15,2) DEFAULT 0.00,
    max_discount DECIMAL(15,2) DEFAULT 0.00,
    quota INT DEFAULT 100,
    used_count INT DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE purchase_order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_order_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(12,4) NOT NULL,
    unit VARCHAR(30) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    received_quantity DECIMAL(12,4) DEFAULT 0.0000,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE purchase_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    supplier_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    po_date DATE NOT NULL,
    due_date DATE NULL,
    status TEXT DEFAULT 'draft',
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    payment_status TEXT DEFAULT 'unpaid',
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE quality_checks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    production_job_id INT NOT NULL,
    inspector_id INT NOT NULL,
    check_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    checklist_json LONGTEXT NULL,
    status TEXT DEFAULT 'passed',
    defect_notes TEXT NULL,
    photo_evidence_path VARCHAR(255) NULL,
    reprint_recommended TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (production_job_id) REFERENCES production_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (inspector_id) REFERENCES users(id)
);

CREATE TABLE quote_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quote_id INT NOT NULL,
    product_id INT NOT NULL,
    item_description VARCHAR(255) NOT NULL,
    specs_json TEXT NULL,
    width_cm DECIMAL(10,2) DEFAULT 0.00,
    height_cm DECIMAL(10,2) DEFAULT 0.00,
    area_sqm DECIMAL(10,4) DEFAULT 0.0000,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit VARCHAR(30) DEFAULT 'pcs',
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cost_estimate DECIMAL(15,2) DEFAULT 0.00,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    finishing_details TEXT NULL,
    notes TEXT NULL,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE quotes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    quote_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    valid_until DATE NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    tax DECIMAL(15,2) DEFAULT 0.00,
    total DECIMAL(15,2) DEFAULT 0.00,
    status TEXT DEFAULT 'draft',
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_system TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP 
);

CREATE TABLE stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    material_id INT NOT NULL,
    movement_type TEXT NOT NULL,
    quantity DECIMAL(12,4) NOT NULL,
    unit_cost DECIMAL(15,2) DEFAULT 0.00,
    balance_after DECIMAL(12,4) NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id INT NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
);

CREATE TABLE stock_opname_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    stock_opname_id INT NOT NULL,
    material_id INT NOT NULL,
    system_stock DECIMAL(12,4) NOT NULL,
    physical_stock DECIMAL(12,4) NOT NULL,
    difference DECIMAL(12,4) NOT NULL,
    unit_cost DECIMAL(15,2) DEFAULT 0.00,
    adjustment_reason VARCHAR(255) NULL,
    FOREIGN KEY (stock_opname_id) REFERENCES stock_opnames(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE stock_opnames (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    opname_number VARCHAR(50) NOT NULL UNIQUE,
    opname_date DATE NOT NULL,
    status TEXT DEFAULT 'draft',
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE stock_transfer_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    stock_transfer_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity_sent DECIMAL(12,4) NOT NULL,
    quantity_received DECIMAL(12,4) DEFAULT 0.0000,
    unit VARCHAR(30) NOT NULL,
    notes VARCHAR(255) NULL,
    FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE stock_transfers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    from_branch_id INT NOT NULL,
    to_branch_id INT NOT NULL,
    from_warehouse_id INT NOT NULL,
    to_warehouse_id INT NOT NULL,
    transfer_number VARCHAR(50) NOT NULL UNIQUE,
    status TEXT DEFAULT 'draft',
    requested_by INT NOT NULL,
    approved_by INT NULL,
    received_by INT NULL,
    transfer_date DATE NOT NULL,
    received_date DATE NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (from_branch_id) REFERENCES branches(id),
    FOREIGN KEY (to_branch_id) REFERENCES branches(id),
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id)
);

CREATE TABLE suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100) NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(120) NULL,
    address TEXT NULL,
    tax_id VARCHAR(50) NULL,
    payment_terms_days INT DEFAULT 30,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    UNIQUE (company_id, code),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE user_branches (
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    PRIMARY KEY (user_id, branch_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NULL,
    role_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(50) NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INT NOT NULL,
    branch_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

PRAGMA foreign_keys = ON;
