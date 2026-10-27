-- Test accounts use the same bcrypt hash for the password "password".
-- This value is for local test data only; never use it for a real account.
INSERT INTO users
    (user_id, first_name, last_name, email, password, birth_date, role)
VALUES
    (1, 'Alice', 'Smith', 'alice@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1990-04-12', 'user'),
    (2, 'Bob', 'Johnson', 'bob@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1992-08-23', 'user'),
    (3, 'Charlie', 'Williams', 'charlie@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'user'),
    (4, 'David', 'Brown', 'david@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'user');

INSERT INTO animals
    (animal_id, name, birth_date, species, breed, health_status, age,
     description, photo, arrival_date, adoption_status, owner_requirements)
VALUES
    (1, 'Buddy', '2020-01-01', 'suns', 'Zeltainais retrīvers', 'Vesels', 6,
     'Draudzīgs un uzticīgs suns.', 'src/img/logo.jpg', '2020-01-01', 'Rezervets',
     'Vēlama pieredze ar suņiem.'),
    (2, 'Whiskers', '2020-02-01', 'kakis', 'Siāmas kaķis', 'Vesels', 6,
     'Rotaļīgs un zinātkārs kaķis.', 'src/img/logo.jpg', '2020-02-01', 'Rezervets',
     'Vēlama pieredze ar kaķiem.'),
    (3, 'Rocky', '2020-03-01', 'suns', 'Vācu aitu suns', 'Vesels', 5,
     'Gudrs un sargājošs suns.', 'src/img/logo.jpg', '2020-03-01', 'Rezervets',
     'Vēlama pieredze ar suņiem.'),
    (4, 'Luna', '2020-04-01', 'kakis', 'Persijas kaķis', 'Vesels', 6,
     'Mīļš un mierīgs kaķis.', 'src/img/logo.jpg', '2020-04-01', 'Pieejams',
     'Vēlama pieredze ar kaķiem.');

INSERT INTO donations
    (donation_id, donor_name, donor_email, amount, payment_method)
VALUES
    (1, 'Alice Smith', 'alice@example.test', 10.00, 'Mastercard'),
    (2, 'Charlie Williams', 'charlie@example.test', 35.00, 'PayPal'),
    (3, 'Bob Johnson', 'bob@example.test', 20.00, 'Internet Bank');

INSERT INTO adoptions (adoption_id, animal_id, user_id, adoption_date, status)
VALUES
    (1, 1, 1, '2020-05-01', 'Gaida'),
    (2, 2, 2, '2020-06-01', 'Apstiprināts'),
    (3, 3, 3, '2020-07-01', 'Noraidīts');
