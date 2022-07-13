-- child_status     | case_status
-- out_of_school    | in_progress 
-- in_observation   | in_progress
-- in_school        | completed
-- cancelled        | cancelled
-- interrupted      | interrupted
-- transferred      | transferred
-- use base_prod_2;
use base_prod_2;
-- SET @child_status='out_of_school' COLLATE utf8_unicode_ci; 
-- SET @cases_status='in_progress' COLLATE utf8_unicode_ci; 

-- SELECT c.alert_status, count(c.alert_status), csa.alert_status, count(csa.alert_status)
-- FROM children c
-- JOIN case_steps_alerta csa ON csa.child_id = c.id 
-- JOIN children_cases cc ON cc.child_id = c.id 
-- where c.child_status = @child_status and cc.case_status = @cases_status group by csa.alert_status, c.alert_status;

SELECT c.id
FROM children c
JOIN case_steps_alerta csa ON csa.child_id = c.id 
JOIN children_cases cc ON cc.child_id = c.id
where c.alert_status <> csa.alert_status