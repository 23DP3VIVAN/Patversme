CREATE DATABASE IF NOT EXISTS animal_shelter
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE animal_shelter;

CREATE TABLE animals (
    animal_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    birth_date DATE NULL,
    species VARCHAR(30) NOT NULL,
    breed VARCHAR(50) NOT NULL,
    health_status VARCHAR(20) NOT NULL,
    age INT NULL,
    description TEXT NOT NULL,
    photo VARCHAR(255) NOT NULL,
    arrival_date DATE NOT NULL,
    adoption_status ENUM('Pieejams', 'Adoptets', 'Rezervets') NOT NULL DEFAULT 'Pieejams',
    owner_requirements TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(40) NOT NULL UNIQUE,
    birth_date DATE NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(60) NOT NULL,
    donor_email VARCHAR(40) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('VISA', 'Apple Pay', 'Google Pay', 'Internet Bank', 'Mastercard', 'PayPal') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE adoptions (
    adoption_id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    user_id INT NOT NULL,
    adoption_date DATE NOT NULL,
    status ENUM('Gaida', 'Apstiprināts', 'Noraidīts') NOT NULL DEFAULT 'Gaida',
    INDEX idx_adoptions_animal_id (animal_id),
    INDEX idx_adoptions_user_id (user_id),
    CONSTRAINT fk_adoptions_animal FOREIGN KEY (animal_id) REFERENCES animals(animal_id),
    CONSTRAINT fk_adoptions_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volunteer_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    activity VARCHAR(100) NOT NULL,
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
