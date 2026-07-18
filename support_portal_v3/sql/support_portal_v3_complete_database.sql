-- ============================================================
-- NAIROBI WATER ICT SUPPORT PORTAL V3
-- Complete database with departments, users, technicians,
-- support categories, demo accounts, sample tickets, and
-- automatic technician assignment by category.
--
-- Demo password for every account: password
-- Database: support_portal_v3
-- ============================================================

CREATE DATABASE IF NOT EXISTS support_portal_v3
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE support_portal_v3;

SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS trg_assign_technician_before_ticket;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS ticket_history;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS technicians;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. DEPARTMENTS
-- ============================================================

CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. USERS
-- ============================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    department_id INT UNSIGNED NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','technician','admin') NOT NULL DEFAULT 'user',
    account_status ENUM('Active','Inactive','Suspended')
        NOT NULL DEFAULT 'Active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. ADMINS
-- ============================================================

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    admin_level ENUM('Super Admin','Administrator')
        NOT NULL DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_admin_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. TECHNICIANS
-- ============================================================

CREATE TABLE technicians (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    technician_type VARCHAR(120) NOT NULL,
    specialization VARCHAR(160) NULL,
    availability ENUM('Available','Busy','Offline')
        NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_technician_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. SUPPORT CATEGORIES
-- ============================================================

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    default_technician_id INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_category_default_technician
        FOREIGN KEY (default_technician_id)
        REFERENCES technicians(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. TICKETS
-- ============================================================

CREATE TABLE tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(40) NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    technician_id INT UNSIGNED NULL,
    category_id INT UNSIGNED NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NULL,
    priority ENUM('Low','Medium','High','Critical')
        NOT NULL DEFAULT 'Medium',
    status ENUM(
        'Open',
        'Assigned',
        'In Progress',
        'Pending',
        'Resolved',
        'Closed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Open',
    assigned_at DATETIME NULL,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    resolution_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ticket_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ticket_technician
        FOREIGN KEY (technician_id)
        REFERENCES technicians(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_ticket_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. TICKET HISTORY
-- ============================================================

CREATE TABLE ticket_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    comment TEXT NULL,
    changed_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_history_ticket
        FOREIGN KEY (ticket_id)
        REFERENCES tickets(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_history_user
        FOREIGN KEY (changed_by)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notification_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_notification_ticket
        FOREIGN KEY (ticket_id)
        REFERENCES tickets(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. DEPARTMENT DATA
-- ============================================================

INSERT INTO departments (name, description) VALUES
('ICT', 'Information and Communication Technology'),
('Finance', 'Finance and accounting'),
('Human Resources', 'Human resources and staff welfare'),
('Customer Service', 'Customer support and service delivery'),
('Operations', 'Water and sewerage operations'),
('Administration', 'General administration'),
('Procurement', 'Purchasing and supplies'),
('Billing', 'Customer billing and revenue'),
('Legal', 'Legal and compliance services'),
('Engineering', 'Engineering and technical services');

-- ============================================================
-- 10. PASSWORD HASH
-- All demo accounts use the password: password
-- This is a valid PHP password_hash() bcrypt value.
-- ============================================================

SET @demo_password =
'$2y$12$mwtbwDV1K5i0LC9QGCRoieT9uRPB22mBU8PxujCzf6epYXTSuBWMu';

-- ============================================================
-- 11. ADMIN AND USER ACCOUNTS
-- ============================================================

INSERT INTO users
(name, email, department_id, password, role, account_status)
VALUES
(
    'System Administrator',
    'admin@supportv3.test',
    1,
    @demo_password,
    'admin',
    'Active'
),
(
    'Demo User',
    'user@supportv3.test',
    2,
    @demo_password,
    'user',
    'Active'
);

INSERT INTO admins (user_id, admin_level)
SELECT id, 'Super Admin'
FROM users
WHERE email = 'admin@supportv3.test';

-- ============================================================
-- 12. TECHNICIAN USER ACCOUNTS
-- ============================================================

INSERT INTO users
(name, email, department_id, password, role, account_status)
VALUES
('Network Technician',
 'network@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Hardware Technician',
 'hardware@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Software Technician',
 'software@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Database Administrator',
 'database@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Cybersecurity Analyst',
 'security@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Systems Administrator',
 'sysadmin@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Printer Technician',
 'printer@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Email Support Technician',
 'email@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Telecommunications Technician',
 'telecom@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('ICT Support Technician',
 'ictsupport@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Cloud Services Technician',
 'cloud@supportv3.test', 1, @demo_password, 'technician', 'Active'),

('Application Support Technician',
 'application@supportv3.test', 1, @demo_password, 'technician', 'Active');

-- ============================================================
-- 13. TECHNICIAN PROFILES
-- ============================================================

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Network Technician',
       'LAN, WAN, routers, switches, Wi-Fi and internet',
       'Available'
FROM users WHERE email = 'network@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Hardware Technician',
       'Computers, laptops, monitors and peripherals',
       'Available'
FROM users WHERE email = 'hardware@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Software Technician',
       'Operating systems and desktop applications',
       'Available'
FROM users WHERE email = 'software@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Database Administrator',
       'MySQL, MariaDB, database access and backups',
       'Available'
FROM users WHERE email = 'database@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Cybersecurity Analyst',
       'Malware, security incidents, accounts and access',
       'Available'
FROM users WHERE email = 'security@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Systems Administrator',
       'Servers, Active Directory and system administration',
       'Available'
FROM users WHERE email = 'sysadmin@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Printer Technician',
       'Printers, scanners and printing services',
       'Available'
FROM users WHERE email = 'printer@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Email Support Technician',
       'Email, Microsoft 365 and collaboration tools',
       'Available'
FROM users WHERE email = 'email@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Telecommunications Technician',
       'IP phones, extensions, CCTV and access control',
       'Available'
FROM users WHERE email = 'telecom@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'ICT Support Technician',
       'General help desk and user support',
       'Available'
FROM users WHERE email = 'ictsupport@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Cloud Services Technician',
       'Cloud services, hosting and online backups',
       'Available'
FROM users WHERE email = 'cloud@supportv3.test';

INSERT INTO technicians
(user_id, technician_type, specialization, availability)
SELECT id, 'Application Support Technician',
       'Business applications, ERP and website support',
       'Available'
FROM users WHERE email = 'application@supportv3.test';

-- ============================================================
-- 14. SUPPORT CATEGORIES
-- Categories are connected to their default technicians.
-- ============================================================

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Network Support',
    'LAN, WAN, routers, switches and network connectivity',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'network@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Internet & Wi-Fi',
    'Internet access and wireless connectivity issues',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'network@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Hardware Support',
    'Desktop, laptop, monitor and hardware problems',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'hardware@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Software Support',
    'Application installation and operating-system errors',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'software@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Database Support',
    'Database access, errors, performance and backups',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'database@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Cybersecurity Support',
    'Malware, suspicious activity and security incidents',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'security@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Account & Password Support',
    'Password resets, account lockouts and access problems',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'security@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Server Administration',
    'Server access, configuration and maintenance',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'sysadmin@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Active Directory',
    'Domain accounts, groups, permissions and policies',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'sysadmin@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Backup & Recovery',
    'Data backups, recovery and restoration requests',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'sysadmin@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Printer Support',
    'Printers, scanners, toner and printing problems',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'printer@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Email Support',
    'Email delivery, mailbox and configuration problems',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'email@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Microsoft 365',
    'Outlook, Teams, OneDrive and Microsoft 365 support',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'email@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'IP Telephony',
    'Desk phones, extensions and voice communication',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'telecom@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'CCTV & Access Control',
    'CCTV cameras, door access and monitoring systems',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'telecom@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Cloud Services',
    'Cloud hosting, online storage and cloud applications',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'cloud@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Website & Web Hosting',
    'Website availability, hosting and web configuration',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'application@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'Application Support',
    'Internal systems and business application support',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'application@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'ERP Support',
    'ERP access, workflow and application errors',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'application@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'System Maintenance',
    'Updates, preventive maintenance and system checks',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'ictsupport@supportv3.test';

INSERT INTO categories
(name, description, default_technician_id)
SELECT
    'General ICT Support',
    'General help desk and uncategorized ICT requests',
    t.id
FROM technicians t
JOIN users u ON u.id = t.user_id
WHERE u.email = 'ictsupport@supportv3.test';

-- ============================================================
-- 15. AUTOMATIC TECHNICIAN ASSIGNMENT
-- This trigger assigns a ticket to the category's default
-- technician when no technician has been selected.
-- It does NOT generate ticket numbers.
-- ============================================================

DELIMITER $$

CREATE TRIGGER trg_assign_technician_before_ticket
BEFORE INSERT ON tickets
FOR EACH ROW
BEGIN
    DECLARE assigned_technician INT UNSIGNED DEFAULT NULL;

    IF NEW.technician_id IS NULL AND NEW.category_id IS NOT NULL THEN
        SELECT default_technician_id
        INTO assigned_technician
        FROM categories
        WHERE id = NEW.category_id
        LIMIT 1;

        IF assigned_technician IS NOT NULL THEN
            SET NEW.technician_id = assigned_technician;
            SET NEW.status = 'Assigned';
            SET NEW.assigned_at = NOW();
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================================
-- 16. SAMPLE TICKETS
-- Ticket numbers remain generated safely in PHP for new tickets.
-- ============================================================

INSERT INTO tickets
(
    ticket_number,
    user_id,
    category_id,
    subject,
    description,
    location,
    priority,
    status
)
SELECT
    'NW-2026-000001',
    u.id,
    c.id,
    'Office Wi-Fi unavailable',
    'The office computer cannot connect to the staff Wi-Fi network.',
    'Administration Block',
    'High',
    'Open'
FROM users u
JOIN categories c
WHERE u.email = 'user@supportv3.test'
AND c.name = 'Internet & Wi-Fi'
LIMIT 1;

INSERT INTO tickets
(
    ticket_number,
    user_id,
    category_id,
    subject,
    description,
    location,
    priority,
    status
)
SELECT
    'NW-2026-000002',
    u.id,
    c.id,
    'Printer showing offline',
    'The Finance department printer is powered on but appears offline.',
    'Finance Office',
    'Medium',
    'Open'
FROM users u
JOIN categories c
WHERE u.email = 'user@supportv3.test'
AND c.name = 'Printer Support'
LIMIT 1;

INSERT INTO tickets
(
    ticket_number,
    user_id,
    category_id,
    subject,
    description,
    location,
    priority,
    status
)
SELECT
    'NW-2026-000003',
    u.id,
    c.id,
    'Email password reset',
    'The user cannot access the company email account.',
    'Customer Service Office',
    'Medium',
    'Open'
FROM users u
JOIN categories c
WHERE u.email = 'user@supportv3.test'
AND c.name = 'Email Support'
LIMIT 1;

-- ============================================================
-- 17. SAMPLE HISTORY
-- ============================================================

INSERT INTO ticket_history
(ticket_id, old_status, new_status, comment, changed_by)
SELECT
    tk.id,
    NULL,
    tk.status,
    'Sample ticket created and automatically assigned.',
    admin_user.id
FROM tickets tk
JOIN users admin_user
    ON admin_user.email = 'admin@supportv3.test';

-- ============================================================
-- 18. SAMPLE NOTIFICATIONS
-- ============================================================

INSERT INTO notifications
(user_id, ticket_id, message)
SELECT
    u.id,
    tk.id,
    CONCAT('Ticket ', tk.ticket_number, ' has been created successfully.')
FROM users u
JOIN tickets tk
WHERE u.email = 'user@supportv3.test';

-- ============================================================
-- 19. USEFUL INDEXES
-- ============================================================

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_priority ON tickets(priority);
CREATE INDEX idx_tickets_user ON tickets(user_id);
CREATE INDEX idx_tickets_technician ON tickets(technician_id);
CREATE INDEX idx_tickets_category ON tickets(category_id);
CREATE INDEX idx_notifications_user_read
    ON notifications(user_id, is_read);

-- ============================================================
-- INSTALLATION COMPLETE
--
-- Admin:
-- admin@supportv3.test / password
--
-- Technicians:
-- network@supportv3.test / password
-- hardware@supportv3.test / password
-- software@supportv3.test / password
-- database@supportv3.test / password
-- security@supportv3.test / password
-- sysadmin@supportv3.test / password
-- printer@supportv3.test / password
-- email@supportv3.test / password
-- telecom@supportv3.test / password
-- ictsupport@supportv3.test / password
-- cloud@supportv3.test / password
-- application@supportv3.test / password
--
-- User:
-- user@supportv3.test / password
-- ============================================================
