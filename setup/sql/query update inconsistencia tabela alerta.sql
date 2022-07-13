SET SQL_SAFE_UPDATES = 0;
USE homestead;
UPDATE homestead.case_steps_alerta csa
JOIN homestead.children c ON csa.child_id = c.id 
SET csa.alert_status = 'rejected'
where c.alert_status = 'rejected' and csa.alert_status = 'pending'