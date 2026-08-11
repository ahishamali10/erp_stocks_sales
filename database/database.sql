CREATE DATABASE IF NOT EXISTS erp_stocks_sales
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE erp_stocks_sales;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS warehouses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_warehouses_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    alert_quantity INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_code (code),
    KEY idx_products_category (category_id),
    KEY idx_products_name (name),
    KEY idx_products_active (is_active),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_products_price CHECK (price >= 0),
    CONSTRAINT chk_products_alert_quantity CHECK (alert_quantity >= 0),
    CONSTRAINT chk_products_is_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS warehouse_products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    warehouse_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_warehouse_products_warehouse_product (warehouse_id, product_id),
    KEY idx_warehouse_products_product (product_id),
    CONSTRAINT fk_warehouse_products_warehouse
        FOREIGN KEY (warehouse_id) REFERENCES warehouses (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_warehouse_products_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_warehouse_products_quantity CHECK (quantity >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_customers_name (name),
    KEY idx_customers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    warehouse_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user_warehouse') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_warehouse (warehouse_id),
    CONSTRAINT fk_users_warehouse
        FOREIGN KEY (warehouse_id) REFERENCES warehouses (id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT chk_users_warehouse_assignment CHECK (
        (role = 'admin' AND warehouse_id IS NULL)
        OR (role = 'user_warehouse' AND warehouse_id IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sales (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_number VARCHAR(100) NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    warehouse_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    subtotal DECIMAL(14,2) NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(14,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sales_invoice_number (invoice_number),
    KEY idx_sales_customer (customer_id),
    KEY idx_sales_warehouse (warehouse_id),
    KEY idx_sales_user (user_id),
    KEY idx_sales_created_at (created_at),
    CONSTRAINT fk_sales_customer
        FOREIGN KEY (customer_id) REFERENCES customers (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_sales_warehouse
        FOREIGN KEY (warehouse_id) REFERENCES warehouses (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_sales_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_sales_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_sales_discount_percentage CHECK (discount_percentage BETWEEN 0 AND 100),
    CONSTRAINT chk_sales_discount_amount CHECK (discount_amount >= 0),
    CONSTRAINT chk_sales_total CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sale_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sale_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sale_items_sale_product (sale_id, product_id),
    KEY idx_sale_items_product (product_id),
    CONSTRAINT fk_sale_items_sale
        FOREIGN KEY (sale_id) REFERENCES sales (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_sale_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_sale_items_unit_price CHECK (unit_price >= 0),
    CONSTRAINT chk_sale_items_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT IGNORE INTO categories (id, name) VALUES
    (1, 'Electronics'),
    (2, 'Office Supplies'),
    (3, 'Accessories');

INSERT IGNORE INTO warehouses (id, name, code) VALUES
    (1, 'Main Warehouse', 'MAIN'),
    (2, 'East Warehouse', 'EAST'),
    (3, 'West Warehouse', 'WEST');

INSERT IGNORE INTO products (id, category_id, code, name, price, alert_quantity, is_active) VALUES
    (1, 1, 'P001', 'Business Laptop', 1500.00, 5, 1),
    (2, 1, 'P002', '24-inch Monitor', 320.00, 6, 1),
    (3, 2, 'P003', 'A4 Copy Paper Box', 42.50, 10, 1),
    (4, 3, 'P004', 'Wireless Mouse', 35.90, 8, 1),
    (5, 3, 'P005', 'USB-C Dock', 119.00, 4, 1),
    (6, 2, 'P006', 'Desk Organizer', 18.75, 3, 0);

INSERT IGNORE INTO warehouse_products (warehouse_id, product_id, quantity) VALUES
    (1, 1, 12), (1, 2, 18), (1, 3, 30), (1, 4, 25), (1, 5, 7), (1, 6, 2),
    (2, 1, 4),  (2, 2, 8),  (2, 3, 9),  (2, 4, 14), (2, 5, 3), (2, 6, 0),
    (3, 1, 7),  (3, 2, 3),  (3, 3, 16), (3, 4, 6),  (3, 5, 9), (3, 6, 1);

INSERT IGNORE INTO customers (id, name, phone, email) VALUES
    (1, 'Acme Trading', '+1 555 0101', 'purchasing@acme.example'),
    (2, 'Northwind Office', '+1 555 0102', 'orders@northwind.example'),
    (3, 'Walk-in Customer', NULL, NULL);

INSERT IGNORE INTO users (id, warehouse_id, name, email, password, role) VALUES
    (1, NULL, 'Demo Administrator', 'admin@example.com', '$2y$10$p269vFsGog3EAPYtlajs1OWhTRGXvO2l.Uez2qUvOEznq0bJhGn3G', 'admin'),
    (2, 1, 'Main Warehouse User', 'warehouse@example.com', '$2y$10$4./0S2rGdIl2PZcyi0w90ONFL3KJmrhKKacgNjZ9W0iPG3VKgN2f6', 'user_warehouse');

COMMIT;
