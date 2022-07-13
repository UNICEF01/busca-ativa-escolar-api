SELECT  TABLE_NAME 'Table Name', ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) 'Size(MB)' FROM information_schema.tables 
WHERE table_schema = "base29052020prod" order by table_schema desc;