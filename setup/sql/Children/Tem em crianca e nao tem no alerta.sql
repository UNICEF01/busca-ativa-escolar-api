SELECT * FROM children c
WHERE c.id NOT IN (SELECT child_id FROM case_steps_alerta) || c.id NOT IN (SELECT child_id FROM case_steps_analise_tecnica)