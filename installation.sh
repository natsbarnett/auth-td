#!/bin/bash

SETCOLOR_FAILURE="\\033[1;31m"
SETCOLOR_SUCCESS="\\033[1;32m"
SETCOLOR_WARNING="\\033[1;33m"
SETCOLOR_NORMAL="\\033[0;39m"
INSTALLDIR="/var/www/html"
REPOLINK="https://github.com/natsbarnett/auth-td/archive/refs/heads/main.zip"
success() {
    echo -e "${SETCOLOR_SUCCESS}$*${SETCOLOR_NORMAL}"
}
error() {
    echo -e "${SETCOLOR_FAILURE}$*${SETCOLOR_NORMAL}"
    exit 1
}
warn() {
    echo -e "${SETCOLOR_WARNING}$*${SETCOLOR_NORMAL}"
}

if [[ $EUID -ne 0 ]]; then
    error "Ce script doit être exécuté avec des privilèges root. Utilisez sudo."
    exit 1
fi

success "+-----------------------------------------------------+"
success "+---         Mise à jour des packages              ---+"
success "+-----------------------------------------------------+"

apt-get -qq update
apt-get -qq upgrade

success "Mises à jour faites"

success "+-----------------------------------------------------+"
success "+---        Installation du stack LAMP             ---+"
success "+-----------------------------------------------------+"
warn "Installation d'Apache..."
apt-get -qq install apache2
service apache2 start
a2enmod rewrite
service apache2 restart

warn "Installation de PHP..."
apt-get -qq install php libapache2-mod-php php-mysql -y

warn "Installation de modules PHP supplémentaires..."
apt-get -qq install php-cli php-curl php-gd php-mbstring php-xml php-zip php-intl -y
phpenmod intl
service apache2 restart

warn "Installation de Maria DB..."
apt-get -qq install mariadb-server
service mariadb start

warn "Installation de zip..."
apt-get -qq install zip -y

warn "Installation de git..."
apt-get -qq install git -y

warn "Installation de node JS..."
apt-get -qq install nodejs npm -y

warn "Vérification de l'installation d'Apache..."
if service apache2 status; then
    success "Apache est installé et fonctionne."
else
    error "Erreur lors de l'installation d'Apache"
fi

success "+-----------------------------------------------------+"
success "+---        Installation des services              ---+"
success "+-----------------------------------------------------+"
# TODO : finir cette merde
warn "Récupération de l'archive"
cd $INSTALLDIR
mkdir td-auth
wget -q $REPOLINK
unzip -q main.zip -d ./td-auth
cd td-auth
mv * ../

warn "Création des clés privées et publiques pour le JWT..."
openssl genpkey -algorithm RSA -out private.pem -pkeyopt rsa_keygen_bits:2048
    openssl rsa -in private.pem -pubout -out public.pem

warn "Création des clés privées et publiques pour le jeton de refresh..."
openssl genpkey -algorithm RSA -out private_refresh.pem -pkeyopt rsa_keygen_bits:2048
openssl rsa -in private_refresh.pem -pubout -out public_refresh.pem

success "       => fait :D"

echo "{}" > tokens.json