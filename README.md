# -LabAct2_Palicte
PHP MySQL Employee &amp; Department Management System. CRUD, search, reports. No CSS, pure HTML tables.

# Employee & Department Management System

A simple PHP/MySQL web application to manage employees and departments. Supports full CRUD operations, search functionality, and a consolidated report view. No CSS or JavaScript frameworks – uses pure HTML for maximum compatibility.

## Features

- **Employee Management**
  - Register new employees (first name, last name, gender, department)
  - Update employee details
  - Delete employees
  - Search employees by first name or last name
- **Department Management**
  - Add departments with name and description
  - Update department information
  - Delete departments (prevented if employees are assigned)
- **Complete Employee Report**
  - View all employees with their department details
  - Search by employee name or department name
- **Home Screen** – central navigation to all modules

## Tech Stack

- **Backend:** PHP (native, no frameworks)
- **Database:** MySQL
- **Frontend:** Plain HTML (no CSS, no JavaScript)

## Installation

### Prerequisites
- Web server with PHP (XAMPP, WAMP, LAMP, or MAMP)
- MySQL database

### Steps

1. **Clone or download** this repository into your web server's document root (e.g., `htdocs` for XAMPP).

2. **Create the database**  
   Open phpMyAdmin or MySQL CLI and run the following SQL script:

```sql
CREATE DATABASE IF NOT EXISTS company_db;
USE company_db;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
);

-- Optional sample data
INSERT INTO departments (name, description) VALUES
('IT', 'Information Technology'),
('HR', 'Human Resources'),
('Sales', 'Sales & Marketing');
