SELECT 
    COUNT(*)
FROM
    children_cases cc
        LEFT JOIN
    daily_metrics dm ON dm.child_id = cc.child_id
WHERE
    dm.child_status = 'cancelled'
        AND cc.cancel_reason IS NOT NULL
        AND dm.case_status = 'cancelled'
        AND (dm.step_slug = '1a_observacao'
        || dm.step_slug = '2a_observacao'
        || dm.step_slug = '3a_observacao'
        || dm.step_slug = '4a_observacao')
        AND (cc.cancel_reason <> 'wrongful_insertion'
        && cc.cancel_reason <> 'duplicate');
