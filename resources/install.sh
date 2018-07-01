#!/bin/bash
touch /tmp/xiaomihome_dep
echo "Début de l'installation"

echo 0 > /tmp/xiaomihome_dep
echo "Installation des dépendances apt"
sudo apt-get -y install python-pip libffi-dev libssl-dev python-cryptography

echo 30 > /tmp/xiaomihome_dep
if [ $(pip list | grep pyudev | wc -l) -eq 0 ]; then
    echo "Installation du module pyudev pour python"
    sudo pip install pyudev
fi

echo 40 > /tmp/xiaomihome_dep
if [ $(pip list | grep requests | wc -l) -eq 0 ]; then
    echo "Installation du module requests pour python"
    sudo pip install requests
fi

echo 50 > /tmp/xiaomihome_dep
if [ $(pip list | grep pyserial | wc -l) -eq 0 ]; then
    echo "Installation du module pyserial pour python"
    sudo pip install pyserial
fi

echo 60 > /tmp/xiaomihome_dep
if [ $(pip list | grep future | wc -l) -eq 0 ]; then
    echo "Installation du module future pour python"
    sudo pip install future
fi

echo 70 > /tmp/xiaomihome_dep
if [ $(pip list | grep pycrypto | wc -l) -eq 0 ]; then
    echo "Installation du module pycrypto pour python"
    sudo pip install pycrypto
fi
echo 80 > /tmp/xiaomihome_dep
if [ $(pip list | grep construct | wc -l) -eq 0 ]; then
    echo "Installation du module construct pour python"
    sudo pip install construct
fi

echo 90 > /tmp/xiaomihome_dep

rm /tmp/xiaomihome_dep

echo "Fin de l'installation"
