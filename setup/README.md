ApÓS A instalação do elastic e do plugin kopf fazer as seguintes configurações:
Entre no endereço http://localhost:19200/_plugin/kopf/#!/cluster
Acesse o menu superior REST;

Na url do bloco REQUEST insira 'children' escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_settings_children.json dentro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Na url do bloco REQUEST insira 'children_daily' escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_settings_children_daily.json dentro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota
{"acknowledged": true}

Na url do bloco REQUEST insira 'cities' escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_settings_cities.json dentro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Na url do bloco REQUEST insira 'schools' escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_settings_schools.json dentro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Pronto a configuração está feita você pode visualizar essa informações no menu 'cluster' escolhendo algum indice como por exemplo o 'children' clicando
na seta para baixo ao lado do nome e escolhendo a opção 'show settings'.

Agora é preciso fazer o mappeamento do indice, para isso entre no menu REST e faça o procedimento a seguir:

Na url do bloco REQUEST insira 'children/child/_mapping' e escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_mapping_children.json centro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Na url do bloco REQUEST insira 'children_daily/child/_mapping' e escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_mapping_children_daily.json centro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Na url do bloco REQUEST insira 'cities/city/_mapping' e escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_mapping_cities.json centro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}

Na url do bloco REQUEST insira 'schools/school/_mapping' e escolha no select a opção 'PUT' e cole o conteúdo do documento
es_index_mapping_schools.json centro do text área que tem o conteúdo '{}', depois clique no botão 'send' você recebera a respota 
{"acknowledged": true}
Após esta configuração o seu elastic está pronto uso, a barra de menus vai estar amarela, para resolver isso clique no menu cluster, 
clique na seta para baixo do menu children e clique em edit settings. No menu index, mude o valor de 1 para 0 no input number_of_replicas

#instalação java manual
wget https://d3pxv6yz143wms.cloudfront.net/8.212.04.1/java-1.8.0-amazon-corretto-jdk_8.212.04-1_amd64.deb
sudo apt-get install java-common
sudo dpkg --install java-1.8.0-amazon-corretto-jdk_8.212.04-1_amd64.deb
sudo update-alternatives --config java
# choose amazon-corretto, if not current choice
sudo update-alternatives --config javac
# choose amazon-corretto, if not current choice

$ java -version
openjdk version "1.8.0_212"
OpenJDK Runtime Environment Corretto-8.212.04.1 (build 1.8.0_212-b04)
OpenJDK 64-Bit Server VM Corretto-8.212.04.1 (build 25.212-b04, mixed mode)

Instalar elasticsearch 2 manual
wget https://download.elastic.co/elasticsearch/release/org/elasticsearch/distribution/deb/elasticsearch/2.3.1/elasticsearch-2.3.1.deb
sudo dpkg -i elasticsearch-2.3.1.deb
sudo systemctl enable elasticsearch.service
/etc/elasticsearch/elasticsearch.yml
network.host: 0.0.0.0

#Comando para ligar o auto-start do elastic 2
sudo update-rc.d elasticsearch defaults 95 10
#Talvez seja necessaŕio rodar o comando abaixo
sudo systemctl enable elasticsearch.service

Instalar Plugin Kopf
https://www.elastic.co/guide/en/elasticsearch/plugins/2.4/installation.html
Acesse a pasta onde foi instalado o elastic
sudo /usr/share/elasticsearch/bin/plugin install lmenezes/elasticsearch-kopf/2.1.1

