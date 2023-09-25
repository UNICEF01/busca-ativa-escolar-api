#!/bin/bash

#A ordem de execução dos caches é a seguinte:

service memcached start
service beanstalkd start

cd /var/www/bae-api && php artisan cache:clear

/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/capitals
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/country
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/reg
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/state

cd /var/www/bae-api/scripts/automatizacoes_go_e_c/c/scripts && python3 dmc.py

/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/bae/cache
cd /var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/bae && python3 maps.py

/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/grafico/grafico
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/grafico/state