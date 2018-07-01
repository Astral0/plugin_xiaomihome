#!/bin/bash
touch /tmp/xiaomihome_dep
echo "Début de l'installation"

echo 0 > /tmp/xiaomihome_dep

sudo apt-get -y install python-pip libffi-dev libssl-dev python-cryptography

echo 30 > /tmp/xiaomihome_dep
sudo pip install pyudev --upgrade --ignore-installed

echo 40 > /tmp/xiaomihome_dep
sudo pip install requests --upgrade --ignore-installed

echo 50 > /tmp/xiaomihome_dep
sudo pip install pyserial --upgrade --ignore-installed

echo 60 > /tmp/xiaomihome_dep
sudo pip install future --upgrade --ignore-installed

echo 70 > /tmp/xiaomihome_dep
sudo pip install pycrypto --upgrade --ignore-installed

echo 80 > /tmp/xiaomihome_dep
sudo pip install construct --upgrade --ignore-installed

echo 90 > /tmp/xiaomihome_dep

rm /tmp/xiaomihome_dep

echo "Fin de l'installation"
