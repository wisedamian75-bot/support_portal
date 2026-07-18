CREATE DATABASE IF NOT EXISTS support_portal_v3
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE support_portal_v3;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS ticket_history;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS technicians;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE departments(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 description VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(160) NOT NULL UNIQUE,
 department_id INT UNSIGNED NULL,
 password VARCHAR(255) NOT NULL,
 role ENUM('user','technician','admin') NOT NULL DEFAULT 'user',
 account_status ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
 last_login DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_users_department
   FOREIGN KEY(department_id) REFERENCES departments(id)
   ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE admins(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL UNIQUE,
 admin_level ENUM('Super Admin','Administrator') NOT NULL DEFAULT 'Administrator',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_admin_user
   FOREIGN KEY(user_id) REFERENCES users(id)
   ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE technicians(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL UNIQUE,
 technician_type VARCHAR(120) NOT NULL,
 specialization VARCHAR(160) NULL,
 availability ENUM('Available','Busy','Offline') NOT NULL DEFAULT 'Available',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_technician_user
   FOREIGN KEY(user_id) REFERENCES users(id)
   ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL UNIQUE,
 description VARCHAR(255) NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tickets(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_number VARCHAR(40) NULL UNIQUE,
 user_id INT UNSIGNED NOT NULL,
 technician_id INT UNSIGNED NULL,
 category_id INT UNSIGNED NULL,
 subject VARCHAR(200) NOT NULL,
 description TEXT NOT NULL,
 location VARCHAR(150) NULL,
 priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
 status ENUM('Open','Assigned','In Progress','Pending','Resolved','Closed','Cancelled') NOT NULL DEFAULT 'Open',
 assigned_at DATETIME NULL,
 resolved_at DATETIME NULL,
 closed_at DATETIME NULL,
 resolution_notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_ticket_user
   FOREIGN KEY(user_id) REFERENCES users(id)
   ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_ticket_technician
   FOREIGN KEY(technician_id) REFERENCES technicians(id)
   ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT fk_ticket_category
   FOREIGN KEY(category_id) REFERENCES categories(id)
   ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket_history(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ticket_id INT UNSIGNED NOT NULL,
 old_status VARCHAR(50) NULL,
 new_status VARCHAR(50) NOT NULL,
 comment TEXT NULL,
 changed_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_history_ticket
   FOREIGN KEY(ticket_id) REFERENCES tickets(id)
   ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_history_user
   FOREIGN KEY(changed_by) REFERENCES users(id)
   ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 ticket_id INT UNSIGNED NULL,
 message VARCHAR(255) NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notification_user
   FOREIGN KEY(user_id) REFERENCES users(id)
   ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_notification_ticket
   FOREIGN KEY(ticket_id) REFERENCES tickets(id)
   ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO departments(name,description) VALUES
('ICT','Information and Communication Technology'),
('Finance','Finance and accounting'),
('Human Resources','Human resources'),
('Customer Service','Customer service'),
('Operations','Operations'),
('Administration','Administration');

INSERT INTO categories(name,description) VALUES
('Network Support','Internet, Wi-Fi and network connectivity'),
('Hardware Support','Desktop, laptop and hardware issues'),
('Software Support','Applications and operating systems'),
('Database Support','Database access and errors'),
('Cybersecurity Support','Security incidents and malware'),
('Printer Support','Printers and peripherals'),
('General ICT Support','General ICT requests');

SET @pw='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

INSERT INTO users(name,email,department_id,password,role) VALUES
('System Administrator','admin@supportv3.test',1,@pw,'admin'),
('Network Technician','network@supportv3.test',1,@pw,'technician'),
('Hardware Technician','hardware@supportv3.test',1,@pw,'technician'),
('Demo User','user@supportv3.test',2,@pw,'user');

INSERT INTO admins(user_id,admin_level)
SELECT id,'Super Admin'
FROM users
WHERE email='admin@supportv3.test';

INSERT INTO technicians(user_id,technician_type,specialization)
SELECT id,'Network Technician','LAN, Wi-Fi and routers'
FROM users
WHERE email='network@supportv3.test';

INSERT INTO technicians(user_id,technician_type,specialization)
SELECT id,'Hardware Technician','Computers, printers and peripherals'
FROM users
WHERE email='hardware@supportv3.test';

INSERT INTO tickets(ticket_number,user_id,technician_id,category_id,subject,description,location,priority,status,assigned_at)
SELECT 'NW-2026-000001',u.id,t.id,c.id,
       'Office Wi-Fi unavailable',
       'The office computer cannot connect to the staff Wi-Fi network.',
       'Administration Block','High','In Progress',NOW()
FROM users u
JOIN technicians t
JOIN categories c
WHERE u.email='user@supportv3.test'
  AND t.technician_type='Network Technician'
  AND c.name='Network Support'
LIMIT 1;

INSERT INTO tickets(ticket_number,user_id,category_id,subject,description,location,priority,status)
SELECT 'NW-2026-000002',u.id,c.id,
       'Printer showing offline',
       'The department printer is powered on but appears offline.',
       'Finance Office','Medium','Open'
FROM users u
JOIN categories c
WHERE u.email='user@supportv3.test'
  AND c.name='Printer Support'
LIMIT 1;

INSERT INTO ticket_history(ticket_id,old_status,new_status,comment,changed_by)
SELECT tk.id,NULL,tk.status,'Sample ticket created',u.id
FROM tickets tk
JOIN users u ON u.email='admin@supportv3.test';

INSERT INTO notifications(user_id,ticket_id,message)
SELECT u.id,tk.id,CONCAT('Sample ticket ',tk.ticket_number,' is available.')
FROM users u
JOIN tickets tk
WHERE u.email='user@supportv3.test'
LIMIT 1;
