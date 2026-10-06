CREATE DATABASE animal_shelter; 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE animals (
    animal_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(30) NOT NULL,
    birth_date DATE NOT NULL,
    species VARCHAR(30) NOT NULL,
    breed VARCHAR(50) NOT NULL,
    health_status VARCHAR(20) NOT NULL,
    age INT,
    description TEXT NOT NULL,
    photo VARCHAR(255) NOT NULL,
    arrival_date DATE NOT NULL,
    adoption_status ENUM('Pieejams', 'Adoptets', 'Rezervets') NOT NULL DEFAULT 'Pieejams',
    owner_requirements TEXT NOT NULL,
);