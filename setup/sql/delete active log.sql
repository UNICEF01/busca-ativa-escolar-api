# Deixa apenas os ultimos 6 meses
SET SQL_SAFE_UPDATES = 0;
USE plataforma_prod;
DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
