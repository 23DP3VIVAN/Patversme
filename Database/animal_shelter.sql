SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS animals (
    animal_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    birth_date DATE NULL,
    species VARCHAR(30) NOT NULL,
    breed VARCHAR(50) NOT NULL,
    gender ENUM('male', 'female') NULL,
    health_status VARCHAR(100) NOT NULL,
    special_needs BOOLEAN NOT NULL DEFAULT FALSE,
    age INT NULL,
    description TEXT NOT NULL,
    photo LONGTEXT NOT NULL,
    arrival_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    adoption_status ENUM('Pieejams', 'Adoptets', 'Rezervets') NOT NULL DEFAULT 'Pieejams',
    owner_requirements TEXT NOT NULL,
    INDEX idx_animals_species_gender (species, gender),
    INDEX idx_animals_breed (breed),
    INDEX idx_animals_health_status (health_status),
    INDEX idx_animals_adoption_status (adoption_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(30) NOT NULL,
    last_name VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    birth_date DATE NULL,
    profile_description VARCHAR(500) NOT NULL DEFAULT '',
    profile_photo MEDIUMTEXT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(60) NOT NULL,
    donor_email VARCHAR(254) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('VISA', 'Apple Pay', 'Google Pay', 'Internet Bank', 'Mastercard', 'PayPal', 'Demonstrācija') NOT NULL,
    status ENUM('not_processed', 'pending', 'succeeded', 'failed') NOT NULL DEFAULT 'not_processed',
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donations_user_id (user_id),
    CONSTRAINT fk_donations_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS adoptions (
    adoption_id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    user_id INT NOT NULL,
    adoption_date DATE NOT NULL,
    completed_at DATETIME NULL,
    application_message TEXT NULL,
    status ENUM('Gaida', 'Izskatīšanā', 'Apstiprināta', 'Pabeigta', 'Noraidīta', 'Atcelta') NOT NULL DEFAULT 'Gaida',
    rejection_reason TEXT NULL,
    INDEX idx_adoptions_animal_id (animal_id),
    INDEX idx_adoptions_user_id (user_id),
    INDEX idx_adoptions_completed_at (completed_at),
    CONSTRAINT fk_adoptions_animal FOREIGN KEY (animal_id) REFERENCES animals(animal_id),
    CONSTRAINT fk_adoptions_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS volunteer_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    activity VARCHAR(100) NOT NULL,
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_processed BOOLEAN NOT NULL DEFAULT FALSE,
    user_id INT NULL,
    INDEX idx_volunteer_applications_user_id (user_id),
    CONSTRAINT fk_volunteer_applications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    topic VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_processed BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favorites (
    user_id INT NOT NULL,
    animal_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, animal_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_animal FOREIGN KEY (animal_id) REFERENCES animals(animal_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
