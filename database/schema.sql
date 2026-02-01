-- SQL table structure
-- Add your database schema here
CREATE DATABASE IF NOT EXISTS loan_app;
USE loan_app;

CREATE TABLE loan_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,

    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),

    national_id VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,

    employment_status ENUM(
        'Employed',
        'Self-Employed',
        'Unemployed'
    ) NOT NULL,

    loan_amount DECIMAL(10,2) NOT NULL,
    repayment_months INT NOT NULL,

    status VARCHAR(20) DEFAULT 'Pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
