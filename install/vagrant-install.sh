#!/bin/bash
# Vagrant Install script for CTF Engine
# Tested on Debian 7 netinstall
#
# Basically syncs up local folder to vm so you can edit locally. 
#
# This creates a mysql server without a root password. 
# Since it's dev listening on localhost ...ehh...questionable.
# 
# Create a local dns entry for ctfdev to vm's ip address
# ( /etc/hosts/ )
#
# define git email and username after running script
# git config --global user.email "YOUR EMAIL ADDRESS"
# git config --global user.name "YOUR NAME"
#
###############################################################

# Install mysql noninteractively
export DEBIAN_FRONTEND=noninteractive

# Get packages. -E grabs the env variables above
sudo apt-get update && apt-get upgrade -y
sudo -E apt-get install -y apache2 mysql-server mysql-client php5 php5-mysql openssl curl git php-pear

# So dangerous... sudo curl into php shell...
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Chown it as apache user
sudo chown -R www-data:www-data /vagrant/

# Install the composer modules
sudo composer install --working-dir /vagrant/include/thirdparty/composer/

# Copy the configs and edit them
sudo cp /vagrant/include/config/config.inc.php.example /vagrant/include/config/config.inc.php
sudo cp /vagrant/include/config/db.inc.php.example /vagrant/include/config/db.inc.php
sudo sed -i -e "s/localhost/ctfdev/" /vagrant/include/config/config.inc.php

# Chown more stuff because we can
sudo chown -R www-data:www-data /vagrant/writable/

# Edit apache2 config
sudo cp /vagrant/install/mellivora.apache.conf /etc/apache2/sites-available/mellivora
sudo sed -i -e "s/ctf.yourdomain.com/ctfdev/" /etc/apache2/sites-available/mellivora
sudo sed -i -e "s/\/var\/www\/mellivora\/htdocs\//\/vagrant\/htdocs\//" /etc/apache2/sites-available/mellivora


# Enable the site
sudo a2dissite 000-default
sudo a2enmod ssl
sudo a2ensite mellivora
sudo service apache2 restart

sudo service mysql restart

# Loadup mysql
echo "CREATE DATABASE mellivora CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql -u root
mysql mellivora -u root < /vagrant/install/mellivora.sql
mysql mellivora -u root < /vagrant/install/countries.sql


