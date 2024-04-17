#!/bin/bash
echo ">> Attempting to elevate priviledges"
if [[ $EUID != 0 ]]; then
    sudo "$0" "$@"
    exit $?
fi

echo ">> Installing Elastic GPG Key"
wget -O - http://packages.elasticsearch.org/GPG-KEY-elasticsearch | apt-key add -

echo ">> Adding deb package"
echo "deb http://packages.elastic.co/elasticsearch/2.x/debian stable main" | sudo tee -a /etc/apt/sources.list.d/elasticsearch-2.x.list

echo ">> Updating apt"
#add-apt-repository ppa:webupd8team/java
apt-get update

echo ">> Pre-agreeing to Oracle License"
echo debconf shared/accepted-oracle-license-v1-1 select true | \
  sudo debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | \
  sudo debconf-set-selections

echo ">> Installing Java and Elastic Search"
apt-get -y install oracle-java8-installer elasticsearch

echo ">> Java Installed"
echo ">> Elastic Search Installed"

echo ">> Scheduling Elasticsearch"
update-rc.d elasticsearch defaults 95 10

echo ">> Starting Elasticsearch"
/etc/init.d/elasticsearch start

echo ">> Running on port 9200. Make sure to add a firewall rule if you need external access."
