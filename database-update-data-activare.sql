-- Adaugă coloana data_activare în tabelul anunturi
-- Rulează în phpMyAdmin sau MySQL Workbench

USE anunturi_db;

-- Verifică dacă coloana există deja și o adaugă dacă nu există
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'anunturi_db' 
AND TABLE_NAME = 'anunturi' 
AND COLUMN_NAME = 'data_activare';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE anunturi ADD COLUMN data_activare DATETIME NULL AFTER data_actualizare',
    'SELECT "Coloana data_activare există deja" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Setează data_activare pentru anunțurile care sunt deja active
UPDATE anunturi 
SET data_activare = data_creare 
WHERE status = 'activ' AND data_activare IS NULL;

-- Creează index pentru data_activare pentru performanță
CREATE INDEX IF NOT EXISTS idx_data_activare ON anunturi(data_activare);

