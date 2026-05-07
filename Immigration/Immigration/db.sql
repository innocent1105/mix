


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) ,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    user_role VARCHAR(200) NOT NULL DEFAULT 'user',
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    nationality VARCHAR(100),
    passport_number VARCHAR(50) UNIQUE,
    visa_type VARCHAR(100),
    visa_expiry_date DATE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type VARCHAR(100),
    document_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- No FOREIGN KEY constraint
);

CREATE TABLE immigration_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    updated_by_admin_id INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- No FOREIGN KEY constraint
);



CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100),
    address_type ENUM('current', 'permanent') DEFAULT 'current'
    -- No FOREIGN KEY constraint
);


CREATE TABLE provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    email VARCHAR(100),
    contact VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE
);


CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    office_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type TEXT,
    description TEXT,
    nationality TEXT,
    destination TEXT,
    contact VARCHAR(50),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Northen', 'Northen Province', 'Kasama.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Luapula', 'Luapula Province', 'luapula.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Muchinga', 'Muchinga Province', 'muchinga.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Eastern', 'Eastern Province', 'eastern.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Central', 'Central Province', 'cental.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Copperbelt', 'Copperbelt Province', 'copper.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'North-Western', 'North-Western Province','northwest.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Western', 'Western Province', 'kuomboka.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Southern', 'Southern Province', 'vic-falls-bridge.jpg', current_timestamp());
INSERT INTO `provinces` (`id`, `name`, `description`, `image`, `created_at`) VALUES (NULL, 'Lusaka', 'Lusaka Province', 'lusaka.jpg', current_timestamp());




