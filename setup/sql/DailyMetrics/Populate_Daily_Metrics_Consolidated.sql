SELECT dm.date, dm.city_id, dm.uf as state, c.region, c.name as city, goal,
COUNT(CASE WHEN dm.child_status = 'in_observation' THEN dm.child_status END) AS 'in_observation',
COUNT(CASE WHEN dm.child_status = 'out_of_school' THEN dm.child_status END) AS 'out_of_school',
COUNT(CASE WHEN dm.child_status = 'in_school' THEN dm.child_status END) AS 'in_school',
COUNT(CASE WHEN dm.child_status = 'interrupted' THEN dm.child_status END) AS 'interrupted',
COUNT(CASE WHEN dm.child_status = 'transferred' THEN dm.child_status END) AS 'transferred',
COUNT(CASE WHEN dm.child_status = 'cancelled' THEN dm.child_status END) AS 'cancelled',
COUNT(CASE WHEN (dm.cancel_reason <> 'duplicate' || dm.cancel_reason <> 'wrongful_insertion') THEN dm.cancel_reason END) AS 'justified_cancelled',
(CASE WHEN g.goal IS NOT NULL THEN true ELSE false END) AS selo
FROM daily_metrics dm 
JOIN cities c ON dm.city_id = c.id
JOIN tenants t ON dm.tenant_id = t.id
left join goals g ON c.ibge_city_id = g.id
where dm.alert_status = 'accepted' AND t.is_registered = 1 AND t.is_registered AND dm.tenant_id = '4c095570-a227-11e8-a1f0-03b2c72268cf'
GROUP BY dm.date, dm.city_id, dm.uf, c.region, c.name, goal