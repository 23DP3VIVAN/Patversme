-- Run once on an existing database before deploying the period statistics.
ALTER TABLE adoptions
    ADD COLUMN completed_at DATETIME NULL AFTER adoption_date,
    ADD INDEX idx_adoptions_completed_at (completed_at);
