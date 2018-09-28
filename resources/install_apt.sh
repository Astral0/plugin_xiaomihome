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
echo "Installation des dépendances apt"
sudo apt-get -y install python-dev python-pip libffi-dev libssl-dev python-cryptography

echo 20 > ${PROGRESS_FILE}
if [ $(pip list | grep construct | wc -l) -eq 0 ]; then
    echo "Installation du module construct pour python"
    sudo pip install construct
fi

echo 30 > ${PROGRESS_FILE}
if [ $(pip list | grep pyudev | wc -l) -eq 0 ]; then
    echo "Installation du module pyudev pour python"
    sudo pip install pyudev
fi

echo 40 > ${PROGRESS_FILE}
if [ $(pip list | grep requests | wc -l) -eq 0 ]; then
    echo "Installation du module requests pour python"
    sudo pip install requests
fi

echo 50 > ${PROGRESS_FILE}
if [ $(pip list | grep pyserial | wc -l) -eq 0 ]; then
    echo "Installation du module pyserial pour python"
    sudo pip install pyserial
fi

echo 60 > ${PROGRESS_FILE}
if [ $(pip list | grep future | wc -l) -eq 0 ]; then
    echo "Installation du module future pour python"
    sudo pip install future
fi

echo 70 > ${PROGRESS_FILE}
if [ $(pip list | grep pycrypto | wc -l) -eq 0 ]; then
    echo "Installation du module pycrypto pour python"
    sudo pip install pycrypto
fi

echo 80 > ${PROGRESS_FILE}
if [ $(pip list | grep enum34 | wc -l) -eq 0 ]; then
    echo "Installation du module enum34 pour python"
    sudo pip install enum34
fi

echo 90 > ${PROGRESS_FILE}
if [ $(pip list | grep enum-compat | wc -l) -eq 0 ]; then
    echo "Installation du module enum-compat pour python"
    sudo pip install enum-compat
fi

echo 100 > /${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
