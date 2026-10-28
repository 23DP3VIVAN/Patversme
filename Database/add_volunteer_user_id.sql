-- Run once on an existing database before deploying profile volunteer history.
ALTER TABLE volunteer_applications
    ADD COLUMN user_id INT NULL AFTER is_processed,
    ADD INDEX idx_volunteer_applications_user_id (user_id),
    ADD CONSTRAINT fk_volunteer_applications_user
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;
