#!/bin/bash

apt-get install dos2unix

# Limpar a tela
clear

# Função para exibir uma mensagem formatada
print_message() {
    echo -e "\n---------------------------------"
    echo "$1"
    echo "---------------------------------"
}

# Limpar a tela
clear

# Iniciar o serviço Memcached
print_message "Iniciando o serviço Memcached"
service memcached start

# Iniciar o serviço Beanstalkd
print_message "Iniciando o serviço Beanstalkd"
service beanstalkd start

# Limpar o cache do Laravel
print_message "Limpando o cache do Laravel"
cd /var/www/bae-api && php artisan cache:clear

# Executar outras etapas
print_message "Executando etapas de Cache..."

# Etapa 1
print_message "Executando Cache PNAD Capitals"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/capitals

# Etapa 2
print_message "Executando Cache PNAD Country"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/country

# Etapa 3
print_message "Executando Cache PNAD Reg"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/reg

# Etapa 4
print_message "Executando Cache PNAD State"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/pnad/state

# Etapa 5
print_message "Executando Cache DMC"
cd /var/www/bae-api/scripts/automatizacoes_go_e_c/c/scripts && python3 dmc.py

# Etapa 6
print_message "Executando Cache BAE Cache"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/bae/cache

# Etapa 7
print_message "Executando Cache Maps"
cd /var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/bae && python3 maps.py

# Etapa 8
print_message "Executando Cache Grafico"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/grafico/grafico


# Etapa 9
print_message "Executando Cache Grafico State"
echo -e "\n"
/var/www/bae-api/scripts/automatizacoes_go_e_c/c/cache/grafico/state
echo -e "\n"

print_message "Processo Concluido"