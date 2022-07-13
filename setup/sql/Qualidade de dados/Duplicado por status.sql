SELECT name, mother_name, COUNT(*) qtd FROM children c
join case_steps_alerta csa ON csa.child_id = c.id
join children_cases cc ON cc.child_id = c.id
where c.alert_status = 'accepted' GROUP BY name, mother_name HAVING qtd > 1;