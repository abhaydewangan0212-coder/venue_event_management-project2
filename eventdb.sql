-- Create Database
CREATE DATABASE IF NOT EXISTS eventdb;
USE eventdb;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Default admin (username: admin, password: admin123)
INSERT INTO admins (username, password)
VALUES ('admin', 'admin123')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Venues table
CREATE TABLE IF NOT EXISTS venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    capacity INT NOT NULL,
    description TEXT
);

-- Events / Packages table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    venue_id INT NOT NULL,
    event_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,       -- price per guest
    description TEXT,
    image VARCHAR(255),                 -- image file name
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
);

-- Food Menu table
CREATE TABLE IF NOT EXISTS food_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price_per_person DECIMAL(10,2) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1
);

-- Example food items (optional)
INSERT INTO food_menu (name, price_per_person, description, is_active) VALUES
('Veg Thali', 350, 'Simple veg thali', 1),
('Non-Veg Thali', 450, 'Basic non-veg thali', 1),
('Full Buffet Veg', 600, 'Veg buffet with starters and dessert', 1),
('Full Buffet Non-Veg', 750, 'Non-veg buffet', 1),
('Premium Wedding Menu', 1200, 'Full premium menu for weddings', 1)
ON DUPLICATE KEY UPDATE price_per_person = VALUES(price_per_person);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    tickets INT NOT NULL,                -- same as guests
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Paid',

    event_date DATE,
    occasion VARCHAR(100),
    theme VARCHAR(100),
    food_menu VARCHAR(100),
    food_cost DECIMAL(10,2),
    room_type VARCHAR(100),
    guests INT,
    total_amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),

    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS event_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    image VARCHAR(200) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

$report_sql = "
    SELECT 
        DATE_FORMAT(booking_date, '%Y-%m') AS month,
        COUNT(*) AS total_bookings,
        COALESCE(SUM(guests), 0) AS total_guests,
        COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM bookings
    WHERE status = 'Paid'
    GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
    ORDER BY DATE_FORMAT(booking_date, '%Y-%m')
";

MariaDB [eventdb]> $report_sql = "
    ">     SELECT
    ">         DATE_FORMAT(booking_date, '%Y-%m') AS month,
    ">         COUNT(*) AS total_bookings,
    ">         COALESCE(SUM(guests), 0) AS total_guests,
    ">         COALESCE(SUM(total_amount), 0) AS total_revenue
    ">     FROM bookings
    ">     WHERE status = 'Paid'
    ">     GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
    ">     ORDER BY DATE_FORMAT(booking_date, '%Y-%m')
    "> ";
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '$report_sql = "
    SELECT
        DATE_FORMAT(booking_date, '%Y-%m') AS mon...' at line 1
MariaDB [eventdb]> UPDATE bookings SET guests = tickets WHERE guests IS NULL OR guests = 0;
ERROR 1054 (42S22): Unknown column 'guests' in 'where clause'
MariaDB [eventdb]> UPDATE bookings SET guests = tickets WHERE guests IS NULL OR guests = 0;
ERROR 1054 (42S22): Unknown column 'guests' in 'where clause'
MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN guests INT AFTER room_type;
ERROR 1054 (42S22): Unknown column 'room_type' in 'bookings'
MariaDB [eventdb]> UPDATE admins SET (username, password) VALUES ('Amisha', 'amisha0710')
    -> ;
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '(username, password) VALUES ('Amisha', 'amisha0710')' at line 1
MariaDB [eventdb]> INSERT INTO admins
    -> (username, password)
    -> VALUES ('Amisha', 'amisha0710')
    -> ;
Query OK, 1 row affected (0.351 sec)

MariaDB [eventdb]> SELECT
    ->     DATE_FORMAT(booking_date, '%Y-%m') AS month,
    ->     COUNT(*) AS total_bookings,
    ->     COALESCE(SUM(total_amount), 0) AS total_revenue
    -> FROM bookings
    -> WHERE status = 'Paid'
    -> GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
    -> ORDER BY DATE_FORMAT(booking_date, '%Y-%m');
ERROR 1054 (42S22): Unknown column 'total_amount' in 'field list'
MariaDB [eventdb]> SELECT
    ->     DATE_FORMAT(booking_date, '%Y-%m') AS month,
    ->     COUNT(*) AS total_bookings,
    ->     COALESCE(SUM(total_amount), 0) AS total_revenue
    -> FROM bookings
    -> WHERE status = 'Paid'
    -> GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
    -> ORDER BY DATE_FORMAT(booking_date, '%Y-%m');
ERROR 1054 (42S22): Unknown column 'total_amount' in 'field list'
MariaDB [eventdb]> INSERT INTO events (..., image, ...)
    -> SELECT image FROM events
    -> ;
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '..., image, ...)
SELECT image FROM events' at line 1
MariaDB [eventdb]> ALTER TABLE events
    ->   ADD COLUMN image VARCHAR(255) NULL AFTER description;
Query OK, 0 rows affected (0.651 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE bookings
    -> ADD event_date DATE NOT NULL;
Query OK, 0 rows affected (0.120 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN event_date DATE AFTER tickets;
ERROR 1060 (42S21): Duplicate column name 'event_date'
MariaDB [eventdb]> ALTER TABLE bookings
    -> DROP COLUMN event_date;
Query OK, 0 rows affected (0.169 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN event_date DATE AFTER tickets;
Query OK, 0 rows affected (0.119 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN event_date DATE NULL AFTER tickets,
    ->   ADD COLUMN occasion VARCHAR(200) NULL AFTER event_date,
    ->   ADD COLUMN theme VARCHAR(200) NULL AFTER occasion,
    ->   ADD COLUMN food_menu VARCHAR(200) NULL AFTER theme,
    ->   ADD COLUMN food_cost DECIMAL(10,2) NULL AFTER food_menu,
    ->   ADD COLUMN room_type VARCHAR(100) NULL AFTER food_cost,
    ->   ADD COLUMN guests INT NULL AFTER room_type,
    ->   ADD COLUMN total_amount DECIMAL(10,2) NULL AFTER guests,
    ->   ADD COLUMN payment_method VARCHAR(100) NULL AFTER total_amount,
    ->   ADD COLUMN transaction_id VARCHAR(200) NULL AFTER payment_method,
    ->   ADD COLUMN status VARCHAR(50) NULL AFTER transaction_id;
ERROR 1060 (42S21): Duplicate column name 'event_date'
MariaDB [eventdb]> ALTER TABLE bookings
    -> DROP COLUMN event_date;
Query OK, 0 rows affected (0.009 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN event_date DATE NULL AFTER tickets,
    ->   ADD COLUMN occasion VARCHAR(200) NULL AFTER event_date,
    ->   ADD COLUMN theme VARCHAR(200) NULL AFTER occasion,
    ->   ADD COLUMN food_menu VARCHAR(200) NULL AFTER theme,
    ->   ADD COLUMN food_cost DECIMAL(10,2) NULL AFTER food_menu,
    ->   ADD COLUMN room_type VARCHAR(100) NULL AFTER food_cost,
    ->   ADD COLUMN guests INT NULL AFTER room_type,
    ->   ADD COLUMN total_amount DECIMAL(10,2) NULL AFTER guests,
    ->   ADD COLUMN payment_method VARCHAR(100) NULL AFTER total_amount,
    ->   ADD COLUMN transaction_id VARCHAR(200) NULL AFTER payment_method,
    ->   ADD COLUMN status VARCHAR(50) NULL AFTER transaction_id;
ERROR 1060 (42S21): Duplicate column name 'status'
MariaDB [eventdb]> SHOW COLUMNS FROM bookings;
+----------------+--------------+------+-----+---------------------+----------------+
| Field          | Type         | Null | Key | Default             | Extra          |
+----------------+--------------+------+-----+---------------------+----------------+
| id             | int(11)      | NO   | PRI | NULL                | auto_increment |
| event_id       | int(11)      | NO   | MUL | NULL                |                |
| customer_name  | varchar(100) | NO   |     | NULL                |                |
| customer_email | varchar(100) | NO   |     | NULL                |                |
| tickets        | int(11)      | NO   |     | NULL                |                |
| booking_date   | datetime     | YES  |     | current_timestamp() |                |
| status         | varchar(20)  | YES  |     | Pending             |                |
+----------------+--------------+------+-----+---------------------+----------------+
7 rows in set (0.231 sec)

MariaDB [eventdb]> ALTER TABLE bookings
    ->   ADD COLUMN IF NOT EXISTS event_date DATE NULL AFTER tickets,
    ->   ADD COLUMN IF NOT EXISTS occasion VARCHAR(200) NULL AFTER event_date,
    ->   ADD COLUMN IF NOT EXISTS theme VARCHAR(200) NULL AFTER occasion,
    ->   ADD COLUMN IF NOT EXISTS food_menu VARCHAR(200) NULL AFTER theme,
    ->   ADD COLUMN IF NOT EXISTS food_cost DECIMAL(10,2) NULL AFTER food_menu,
    ->   ADD COLUMN IF NOT EXISTS room_type VARCHAR(100) NULL AFTER food_cost,
    ->   ADD COLUMN IF NOT EXISTS guests INT NULL AFTER room_type,
    ->   ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) NULL AFTER guests,
    ->   ADD COLUMN IF NOT EXISTS payment_method VARCHAR(100) NULL AFTER total_amount,
    ->   ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(200) NULL AFTER payment_method;
Query OK, 0 rows affected (0.826 sec)
Records: 0  Duplicates: 0  Warnings: 0

 CREATE TABLE users (
    ->     id INT AUTO_INCREMENT PRIMARY KEY,
    ->     name VARCHAR(100) NOT NULL,
    ->     email VARCHAR(100) NOT NULL UNIQUE,
    ->     password VARCHAR(255) NOT NULL,
    ->     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -> );
ERROR 1046 (3D000): No database selected
MariaDB [(none)]> SHOW DATABASES;
+--------------------+
| Database           |
+--------------------+
| eventdb            |
| information_schema |
| mysql              |
| performance_schema |
| phpmyadmin         |
| test               |
+--------------------+
6 rows in set (0.402 sec)

MariaDB [(none)]> USE eventdb
Database changed
MariaDB [eventdb]> CREATE TABLE users (
    ->     id INT AUTO_INCREMENT PRIMARY KEY,
    ->     name VARCHAR(100) NOT NULL,
    ->     email VARCHAR(100) NOT NULL UNIQUE,
    ->     password VARCHAR(255) NOT NULL,
    ->     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -> );
ERROR 1050 (42S01): Table 'users' already exists
MariaDB [eventdb]> SHOW tables;
+-------------------+
| Tables_in_eventdb |
+-------------------+
| admins            |
| bookings          |
| event_images      |
| events            |
| food_menu         |
| reviews           |
| users             |
| venues            |
+-------------------+
8 rows in set (0.227 sec)

MariaDB [eventdb]> DESCRIBE users;
+------------+--------------+------+-----+---------------------+----------------+
| Field      | Type         | Null | Key | Default             | Extra          |
+------------+--------------+------+-----+---------------------+----------------+
| id         | int(11)      | NO   | PRI | NULL                | auto_increment |
| name       | varchar(100) | NO   |     | NULL                |                |
| email      | varchar(100) | NO   | UNI | NULL                |                |
| password   | varchar(255) | NO   |     | NULL                |                |
| created_at | datetime     | YES  |     | current_timestamp() |                |
+------------+--------------+------+-----+---------------------+----------------+
5 rows in set (0.671 sec)

MariaDB [eventdb]> SELECT * FROM users;
+----+-------------------+------------------------------+-----------------+---------------------+
| id | name              | email                        | password        | created_at          |
+----+-------------------+------------------------------+-----------------+---------------------+
|  1 | Kiran Magre       | wwwmagrekiran312@gmail.com   | 7654321         | 2025-12-09 11:02:35 |
|  2 | Dimple Sonwani    | wwwdimple123@gmail.com       | dimple213       | 2025-12-09 11:03:08 |
|  3 | Saksham Sharma    | sharmast07@gmail.com         | ast0710         | 2025-12-09 11:07:47 |
|  4 | Nidhi Sahu        | nidhi123@gmail.com           | 1234567         | 2025-12-09 14:08:39 |
|  5 | Samiya Noor Rizvi | samiyarizvi12@gmail.com      | samiyanoorrizvi | 2025-12-09 14:14:44 |
|  6 | sambaby           | sambaby@gmail.com            | sam123          | 2025-12-09 14:16:22 |
|  7 | Amisha Sharma     | wwwsharmaamisha311@gmail.com | amisha123       | 2025-12-10 08:50:28 |
+----+-------------------+------------------------------+-----------------+---------------------+
7 rows in set (0.791 sec)

MariaDB [eventdb]> INSERT INTO users (name, email, password)
    -> VALUES ('Muskan Sahu', 'muskan02@gmail.com', '123456');
Query OK, 1 row affected (0.595 sec)

MariaDB [eventdb]> ALTER TABLE users ADD status ENUM('active','inactive') DEFAULT 'active';
Query OK, 0 rows affected (1.558 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE users
    -> ADD contact VARCHAR(15) NOT NULL AFTER email;
Query OK, 0 rows affected (0.222 sec)
Records: 0  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> ALTER TABLE users ADD UNIQUE(contact);
ERROR 1062 (23000): Duplicate entry '' for key 'contact'
MariaDB [eventdb]> UPDATE users
    -> SET contact = CONCAT('987654321', id)
    -> WHERE contact = '' OR contact IS NULL;
Query OK, 8 rows affected (0.162 sec)
Rows matched: 8  Changed: 8  Warnings: 0

MariaDB [eventdb]> ALTER TABLE users MODIFY contact VARCHAR(10) NOT NULL;
Query OK, 8 rows affected (0.705 sec)
Records: 8  Duplicates: 0  Warnings: 0

MariaDB [eventdb]> SELECT id, name, email, contact FROM users;
+----+-------------------+------------------------------+------------+
| id | name              | email                        | contact    |
+----+-------------------+------------------------------+------------+
|  1 | Kiran Magre       | wwwmagrekiran312@gmail.com   | 9876543211 |
|  2 | Dimple Sonwani    | wwwdimple123@gmail.com       | 9876543212 |
|  3 | Saksham Sharma    | sharmast07@gmail.com         | 9876543213 |
|  4 | Nidhi Sahu        | nidhi123@gmail.com           | 9876543214 |
|  5 | Samiya Noor Rizvi | samiyarizvi12@gmail.com      | 9876543215 |
|  6 | sambaby           | sambaby@gmail.com            | 9876543216 |
|  7 | Amisha Sharma     | wwwsharmaamisha311@gmail.com | 9876543217 |
|  8 | Muskan Sahu       | muskan02@gmail.com           | 9876543218 |
+----+-------------------+------------------------------+------------+
8 rows in set (0.109 sec)

MariaDB [eventdb]> UPDATE users
    -> SET contact = '9876543219'
    -> WHERE id = 1;
Query OK, 1 row affected (0.300 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    ->     -> SET contact = '9876543219'
    ->
    -> ;
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '-> SET contact = '9876543219'' at line 2
MariaDB [eventdb]> UPDATE users
    -> SET contact = '9213213213'
    -> WHERE id = 2;
Query OK, 1 row affected (0.117 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '9180218021'
    -> WHERE id = 3;
Query OK, 1 row affected (0.094 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '9212212212'
    -> WHERE id = 4;
Query OK, 1 row affected (0.008 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '9156156156'
    -> WHERE id = 5;
Query OK, 1 row affected (0.106 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '987654321'
    -> WHERE id = 6;
Query OK, 1 row affected (0.052 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> ALTER TABLE users
    -> ADD CONSTRAINT
    -> chk_contact-10-digits
    -> CHECK (contact REGEXP '^[0-9]{10}$');
ERROR 1064 (42000): You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '-10-digits
CHECK (contact REGEXP '^[0-9]{10}$')' at line 3
MariaDB [eventdb]> UPDATE users
    -> SET contact = '9876543211'
    -> WHERE id = 6;
Query OK, 1 row affected (0.149 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '8959869233'
    -> WHERE id = 7;
Query OK, 1 row affected (0.130 sec)
Rows matched: 1  Changed: 1  Warnings: 0

MariaDB [eventdb]> UPDATE users
    -> SET contact = '9232323239'
    -> WHERE id = 8;
Query OK, 1 row affected (0.144 sec)
Rows matched: 1  Changed: 1  Warnings: 0


