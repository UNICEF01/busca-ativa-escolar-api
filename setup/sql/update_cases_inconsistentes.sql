-- Cancelados
use base05042020test;
SET @child_status='cancelled' COLLATE utf8_unicode_ci; 
SET @cases_status='cancelled' COLLATE utf8_unicode_ci; 
SET @cases_status_update='cancelled' COLLATE utf8_unicode_ci;

UPDATE children_cases cc
JOIN children c ON cc.child_id = c.id 
SET cc.case_status = @cases_status_update
where c.child_status = @child_status and cc.case_status <> @cases_status