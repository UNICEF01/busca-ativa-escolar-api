#!/bin/bash

#remove e cria o indice children. 

# Definindo URL base
url='http://elasticsearch.test/children'

# Limpando o console
clear 
tput reset

# Verificando se o índice children existe
index_exists=$(curl -s -o /dev/null -w "%{http_code}" "$url") 

if [ $index_exists == "200" ]; then
  # Índice existe, deletando
  echo "Índice children existe, deletando..."
  curl -X DELETE "$url"

  # Aguardando 1 segundo para deletar completamente
  sleep 2 
fi

echo -e "\n\nIniciando a criação do índice Children"

# Lendo configurações do arquivo JSON
settings=$(cat es_index_settings_children.json)

echo -e "\nCriando o índice Children\n" 
# Criando o índice com as configurações padrão
curl -X PUT -H "Content-Type: application/json" -d "$settings" "$url"

# Definindo número de réplicas como 0
settings='{"number_of_replicas": 0}' 

echo -e "\n\nAtualizando configurações do índice Children\n"
# Atualizando configurações do índice existente
curl -X PUT -H "Content-Type: application/json" -d "$settings" "$url/_settings"

# Lendo mappings do arquivo JSON 
mapping=$(cat es_index_mapping_children.json)

# Concatenando URL com caminho para mappings
url="$url/child/_mapping"

echo -e "\n\nAplicando mappings no índice Children\n"
# Aplicando mappings no índice 
curl -X PUT -H "Content-Type: application/json" -d "$mapping" "$url"

echo -e "\n\nProcesso concluído!\n"