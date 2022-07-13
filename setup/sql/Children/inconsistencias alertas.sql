-- child_status     | case_status
-- out_of_school    | in_progress
-- in_observation   | in_progress
-- in_school        | completed
-- cancelled        | cancelled
-- interrupted      | interrupted
-- transferred      | transferred
-- use base_prod_2;
use base05042020prod;
SET @child_status='interrupted' COLLATE utf8_unicode_ci; 
SET @cases_status='cancelled' COLLATE utf8_unicode_ci; 

SELECT c.alert_status, count(c.alert_status), csa.alert_status, count(csa.alert_status)
FROM children c
JOIN case_steps_alerta csa ON csa.child_id = c.id 
JOIN children_cases cc ON cc.child_id = c.id 
where c.child_status = @child_status group by csa.alert_status, c.alert_status