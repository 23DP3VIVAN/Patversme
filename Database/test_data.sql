INSERT INTO users (id, name, last_name, email, password) VALUES
(1, 'Alice', 'Smith', 'alice@example.com', 'password123'),
(2, 'Bob', 'Johnson', 'bob@example.com', 'password456'),
(3, 'Charlie', 'Williams', 'charlie@example.com', 'password789'),
(4, 'David', 'Brown', 'david@example.com', 'password012');

INSERT INTO animals (id, name, birth_date, species, breed, health_status, age, description, arrival_date, adoption_status, owner_requirements) VALUES
(1, 'Buddy', '2020-01-01', 'Dog', 'Golden Retriever', 'Healthy', 3, 'Friendly and loyal', '2020-01-01', 'Available', 'Experience with dogs required'),
(2, 'Whiskers', '2020-02-01', 'Cat', 'Siamese', 'Healthy', 2, 'Playful and curious', '2020-02-01', 'Available', 'Experience with cats required'),
(3, 'Rocky', '2020-03-01', 'Dog', 'German Shepherd', 'Healthy', 4, 'Protective and intelligent', '2020-03-01', 'Available', 'Experience with dogs required'),
(4, 'Luna', '2020-04-01', 'Cat', 'Persian', 'Healthy', 1, 'Cute and affectionate', '2020-04-01', 'Available', 'Experience with cats required');

INSERT INTO donations (id, donor_name, donor_email, amount, payment_method) VALUES
(1, 'Alice Smith', 'alice@example.com', 10.00, 'Mastercard'),
(2, 'Charlie Williams', 'charlie@example.com', 35.00, 'PayPal'),
(3, 'Bob Johnson', 'bob@example.com', 20.00, 'Internet Bank');

INSERT INTO adoptions (id, animal_id, user_id, adoption_date) VALUES
(1, 1, 1, '2020-05-01'),
(2, 2, 2, '2020-06-01'),
(3, 3, 3, '2020-07-01');