#!/bin/bash
PROGRESS_FILE=/tmp/dependancy_xiaomihome_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
sudo apt-get update
echo 10 > ${PROGRESS_FILE}

sudo apt-get -y install python-dev python-pip libffi-dev libssl-dev python-cryptography

echo 20 > ${PROGRESS_FILE}
sudo pip install construct --upgrade --ignore-installed

echo 30 > ${PROGRESS_FILE}
sudo pip install pyudev --upgrade --ignore-installed

echo 40 > ${PROGRESS_FILE}
sudo pip install requests --upgrade --ignore-installed

echo 50 > ${PROGRESS_FILE}
sudo pip install pyserial --upgrade --ignore-installed

echo 60 > ${PROGRESS_FILE}
sudo pip install future --upgrade --ignore-installed

echo 70 > ${PROGRESS_FILE}
sudo pip install pycrypto --upgrade --ignore-installed

echo 80 > ${PROGRESS_FILE}
sudo pip install enum34 --upgrade --ignore-installed

echo 90 > ${PROGRESS_FILE}
sudo pip install enum-compat --upgrade --ignore-installed

echo 100 > ${PROGRESS_FILE}

echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
