-- ADMINS
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

-- DRIVERS
CREATE TABLE drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    license_number VARCHAR(50),
    status ENUM('Active', 'Suspended') DEFAULT 'Active'
);

-- PARENTS
CREATE TABLE parents (
    parent_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255)
);

-- USERS (Auth table, not foreign-key linked to admins/drivers/parents)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'driver', 'parent') NOT NULL,
    related_id INT NOT NULL -- This ID refers to admin_id, driver_id, or parent_id depending on role
);

-- ROUTES
CREATE TABLE routes (
    route_id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- STOPS
CREATE TABLE stops (
    stop_id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT,
    stop_name VARCHAR(100) NOT NULL,
    arrival_time TIME,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
);

-- BUSES
CREATE TABLE buses (
    bus_id INT PRIMARY KEY AUTO_INCREMENT,
    bus_number VARCHAR(50) NOT NULL,
    capacity INT,
    route_id INT,
    driver_id INT,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL
);

-- STUDENTS
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50),
    contact VARCHAR(20),
    eligible BOOLEAN DEFAULT TRUE,
    stop_id INT,
    route_id INT,
    parent_id INT,
    FOREIGN KEY (stop_id) REFERENCES stops(stop_id) ON DELETE SET NULL,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES parents(parent_id) ON DELETE SET NULL
);

-- BUS ATTENDANCE
CREATE TABLE bus_attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    route_id INT,
    date DATE,
    status ENUM('Present', 'Absent'),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
);


ALTER TABLE students ADD status VARCHAR(50) DEFAULT 'Not Available';
