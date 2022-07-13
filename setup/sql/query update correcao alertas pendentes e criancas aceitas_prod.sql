SET SQL_SAFE_UPDATES = 0;
USE plataforma_prod;
UPDATE plataforma_prod.case_steps_alerta csa
JOIN plataforma_prod.children c ON csa.child_id = c.id 
SET csa.alert_status = 'accepted'
where c.alert_status = 'accepted' and csa.alert_status = 'pending'