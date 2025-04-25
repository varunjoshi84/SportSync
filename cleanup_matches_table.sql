-- SQL Script to clean up the matches table structure by altering the existing table
-- Updated: April 23, 2025

-- Select the database first
USE sport_sync;

-- -- First, let's back up the matches table for safety
DROP TABLE IF EXISTS matches_backup;
CREATE TABLE matches_backup AS SELECT * FROM matches;

-- -- Add the new football-specific columns if they don't already exist and remove unnecessary columns
-- -- Remove team shots and possession columns as they are not being used
ALTER TABLE matches 
DROP COLUMN IF EXISTS team1_shots,
DROP COLUMN IF EXISTS team2_shots,
DROP COLUMN IF EXISTS team1_possession,
DROP COLUMN IF EXISTS team2_possession;

-- -- Log the successful completion
-- SELECT 'Matches table structure updated with all required columns and unnecessary columns removed' AS 'Result';
