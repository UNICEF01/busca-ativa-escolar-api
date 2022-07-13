# correção alertas pendentes e crianças aceitas
# O correto no caso de criança accepted é o alert_status da tabela
# case_steps_alerta e children tenham o valor accepted
SET SQL_SAFE_UPDATES = 0;
USE plataforma_prod;
UPDATE plataforma_prod.case_steps_alerta csa
JOIN plataforma_prod.children c ON csa.child_id = c.id 
SET csa.alert_status = 'rejected'
where c.alert_status = 'rejected' and csa.alert_status = 'pending'