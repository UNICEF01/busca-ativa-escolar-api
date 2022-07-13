-- DELETE from activity_log where action = 'updated';
DELETE from notifications where type = 'BuscaAtivaEscolar\\Notifications\\ChildUpdated';
-- OPTIMIZE TABLE activity_log;
-- OPTIMIZE TABLE notifications;