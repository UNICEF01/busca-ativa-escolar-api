#!/bin/bash

#A ordem de execução dos caches é a seguinte:

cd /var/www/bae-api && php artisan cache:clear &&

cd /var/www/bae-arquivos/automatizacoes_go_e_c/c/scripts && pypy3 dmc.py //SOMENTE UMA VEZ NO DIA

/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/grafico/grafico &&
/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/grafico/state &&

/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/pnad/country &&
/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/pnad/reg &&
/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/pnad/state &&
/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/pnad/capitals &&

/var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/bae/cache
# cd /var/www/bae-arquivos/automatizacoes_go_e_c/c/cache/bae && pypy3 maps.py
