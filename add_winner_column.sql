-- Add winner column to matches table
ALTER TABLE matches ADD COLUMN winner VARCHAR(100) DEFAULT NULL AFTER status;

-- Update existing completed matches based on scores
UPDATE matches 
SET winner = 
    CASE 
        WHEN team1_score > team2_score THEN team1
        WHEN team2_score > team1_score THEN team2
        WHEN team1_score = team2_score AND status = 'completed' THEN 'Draw'
        ELSE NULL
    END
WHERE status = 'completed';