SELECT c.id, c.name, c.mother_name, csa.alert_status, c.alert_status, c.child_status, cc.case_status FROM base05042020prodmin.children c
join case_steps_alerta csa ON csa.child_id = c.id
join children_cases cc ON cc.child_id = c.id
where c.name = '-- informação não disponível --'