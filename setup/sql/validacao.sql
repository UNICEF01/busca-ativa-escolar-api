# Quanto ao alerta pode ter 3 status pending, accepted e rejected que devem estar iguais nas tabelas case_steps_alerta e children 
SELECT c.id, cc.current_step_type, csa.is_completed, csa.alert_status as AlertStatus, c.alert_status as ChildrenAlertStatus, cc.case_status, c.child_status FROM plataforma_prod.children c
JOIN plataforma_prod.case_steps_alerta csa ON csa.child_id = c.id 
JOIN plataforma_prod.children_cases cc ON cc.child_id = c.id 
where csa.alert_status = 'pending' and c.alert_status = 'rejected'