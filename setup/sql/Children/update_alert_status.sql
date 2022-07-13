-- child_status     | case_status | accepted
-- out_of_school    | in_progress | rejected
-- in_observation   | in_progress | pending
-- in_school        | completed
-- cancelled        | cancelled
-- interrupted      | interrupted
-- transferred      | transferred
-- Crianca cancelada e caso em progresso = caso recebe o status cancelado
use base_prod_2;
SET @status_update='accepted' COLLATE utf8_unicode_ci;

UPDATE children_cases cc
JOIN children c ON cc.child_id = c.id 
JOIN case_steps_alerta csa ON csa.child_id = c.id 
SET csa.alert_status = @status_update
where c.alert_status <> csa.alert_status