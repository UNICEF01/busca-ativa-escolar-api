SELECT name, mother_name, COUNT(*) QTD FROM children GROUP BY name, mother_name HAVING QTD > 1;
