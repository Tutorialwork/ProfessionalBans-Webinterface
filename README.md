# ProfessionalBans Webinterface
NEW (Fixed and better) Webinterface for the Bansystem ProfessionalBans Reloaded!

# Table of contents

1. [Installtion on common Linux server](#Installing)
2. [Troubleshooting](#Troubleshooting)
    - Trouble with Composer
    - Installing webinterface at webspace without SSH access

# How to setup the new webinterface?

### Recommendations for server setup

Use at least ``Ubuntu 20.04`` or ``Debian 10/11``<br>
Installing ``MariaDB 10.3.X`` or higher instead of ``MySQL``<br>
Using at least ``PHP 7.4.X`` or higher

### Installing

**IMPORTANT**: Please setup first the Minecraft plugin!

-  Setting up your Apache2 server for the new webinterface. Open the Apache2 settings file with `nano /etc/apache2/sites-available/pbans.conf` and add this to your file. You need to create first a subdomain by your domain hosting::

```
<VirtualHost *:80>
        ServerName professionalbans.yourdomain.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/professionalbans/public 

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
- [Install composer or update if necessary](https://getcomposer.org/download/)
- Install dependencies `apt install php-intl php-gd php-xml php-mbstring php-mysql`
- Create folder in webserver `mkdir /var/www/professionalbans && cd /var/www/professionalbans`
- Download webinterface `git clone https://github.com/Dotta4You/ProfessionalBans-Webinterface-Fixed .`
- Install webinterface dependencies `composer install`
- Install compatibility with Apache2 `composer require symfony/apache-pack` and confirm with yes (y)
- Gave webserver permissions ``chown -R www-data:www-data /var/www/professionalbans``
- Enable Apache2 mod_rewrite ``sudo a2enmod rewrite``
- Restart webserver `service apache2 restart`

Done - now you can access your webinterface and setup it.

# Troubleshooting

## I get a error by running composer commands like this ``[ErrorException] "continue" targeting switch is equivalent to "break". Did you mean to use "continue 2"?``
##### Try reinstalling Composer with
- ``sudo apt-get remove composer`` 
- ``sudo apt autoremove`` 
- ``sudo curl -s https://getcomposer.org/installer | php`` 
- ``sudo mv composer.phar /usr/local/bin/composer`` 

## I'm using a webspace without SSH access
##### Installing Composer at Windows
- [Download XAMPP for Composer](https://www.apachefriends.org/de/index.html)
- [Download Composer for Windows](https://getcomposer.org/Composer-Setup.exe)
- Download webinterface using Windows shell using ``git clone https://github.com/Dotta4You/ProfessionalBans-Webinterface-Fixed``
- Open downloaded folder
- Open Windows shell at this folder like this ![Alt text](https://i.imgur.com/Hn4aB1i.png?raw=true "Optional Title")
- Install webinterface dependencies `composer install`
- Install compatibility with Apache2 `composer require apache-pack` and confirm with yes (y)
- Upload all files to your webspace. **This can take a long time.**

## The language setting won't work
##### Clear cache

- Go in the root directory with ``cd /var/www/professionalbans``
- Clear cache with ``php bin/console cache:clear``

# REST API

ProfessionalBans is developer friendly. You can use the REST API to access all data from the webinterface over the api.

How to use?

- You can access the api with `/api/login` for example, a full documentation is coming soon.
- All what you need is your API key
