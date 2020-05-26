# HINWEIS: Die Installation wird auf einer frisch installierten virutellen Maschine mit Ubuntu 20.04 als Betriebssystem durchgeführt!
# Ich übernehme keine Verantwortung für entstandene Schäden! Es muss nicht sein, dass das Tutorial bei allen funktioniert, allerdings sind das alle Schritte die bei mir zur Lösung geführt haben.

### Situation: Ein frisch installierter Linux Server

## Fangen wir also an...

## Installation von Apache und PHP

#### Zuerst die Systempakete aktualisieren
* `sudo apt-get update && sudo apt-get upgrade -y`

#### Danach die benötigten Pakete für einen Apache-Webserver und PHP installieren
* `sudo apt install -y ufw apache2 mysql-server php-fpm bindfs php-mysql`

#### Nun können wir noch die Firewall aktivieren (Muss man meiner Meinung nach nicht machen)
#### Hier aktiviere ich erst den Zugriff per ssh, http und https und aktiviere dann die Firewall
* `sudo ufw allow ssh`
* `sudo ufw allow http`
* `sudo ufw allow https`
* `sudo ufw --force enable`

#### PHP-FPM konfigurieren
* `sudo a2enmod proxy_fcgi`
* `sudo a2enconf php7.4-fpm`
* `sudo systemctl restart apache2`

#### Rechte auf /var/www/ anpassen
* `sudo chown -R www-data:www-data /var/www/`
* `sudo find /var/www/ -type d -exec chmod g+s {} +`

#### Nun wird ein Ordner mit dem Namen `websites` im home-Verzeichnis erstellt in dem dann alle Dateien installiert werden (Hier liegen die Dateien des Bansystems)
* `mkdir ~/websites`
* `printf "\n\nbindfs#/var/www/ /home/$USER/websites/ fuse force-user=$USER,force-group=$USER,create-for-user=www-data,create-for-group=www-data,create-with-perms=0770,chgrp-ignore,chown-ignore,chmod-ignore 0 0" | sudo tee -a /etc/fstab`
* `sudo mount /home/$USER/websites`
* Nun noch mit `echo "<?php phpinfo();" > ~/websites/html/phpinfo.php` noch eine Php-Info Datei erstellen um zu schauen ob PHP funktioniert
* Jetzt müsste man im Browser schonmal auf `http://domain.de/phpinfo.php` navigieren können und sollte die PHP-Info-Übersicht sehen (Also funktioniert PHP)






## Installation des ProfessionalBans-Webinterface
#### Szenario: Wir haben mehrere Webseiten auf dem Server laufen
#### Szenario: Wir wollen das Bansystem nach `~/websites/bansystem` installieren und es später über `http://domain.de/bans` erreichen
* Zuerst lege ich einen Link von `/var/www/html/bans` nach `/var/www/bansystem/public` an. Dazu benutze ich:
  * `sudo ln -s /var/www/bansystem/public /var/www/html/bans`
* Nun mit `sudo apt-get install composer` den Composer installieren (Dieser wird benötigt um das Webinterface zu installieren)
* Danach mit `sudo apt-get install php-intl php-gd php-xml php-mbstring` die Abhängigkeiten für das Webinterface installieren
* Jetzt mit `mkdir ~/websites/bansystem && cd ~/websites/bansystem` einen Ordner in unserem Home-Verzeichnis erstellen in dem das Bansystem installiert wird
* Nun noch mit `git clone https://github.com/Tutorialwork/ProfessionalBans-Webinterface .` (Ja der Punkt gehört dazu) das Bansystem runterladen
* Anschließend mit `composer install` das Bansystem installieren
* Nun `composer require symfony/apache-pack` ausführen (Wenn die Frage kommt, mit einem `y` bestätigen)
* Jetzt noch `sudo a2enmod rewrite` ausführen und die Installation ist fast geschafft

#### In der Datei `/etc/apache2/sites-available/000-default.conf` muss jetzt noch folgendes nach `DocumentRoot /hier/steht/ein/dateipfad` eingetragen werden
```
<Directory /hier/wieder/der/dateipfad>
    AllowOverride All
    Order Allow,Deny
    Allow from All
</Directory>
```

#### Fast fertig :)
* Wir erstellen gleich im public-Verzeichnis eine neue .htaccess Datei. Wenn ihr die alte behalten wollt, gebt nun folgendes ein:
  * `mv ~/websites/bansystem/public/.htaccess ~/websites/bansystem/public/old_htaccess_file`
* Danach mit `nano ~/websites/bansystem/public/.htaccess` einen neuen Texteditor öffnen
* In diesen Texteditor schreibt ihr foldendes rein:
```
<IfModule mod_rewrite.c>
      <IfModule mod_negotiation.c>
           Options -MultiViews
       </IfModule>
    Options +FollowSymlinks
    RewriteEngine On
    RewriteBase /bans
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME) !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

* Zu guter letzt wird noch mit `sudo systemctl restart apache2` der Apache-Server neugestartet um die Änderungen zu übernehmen

## Glückwunsch! Nun sollte die Installation des ProfessionalBans-Webinterface abgeschlossen sein und eure Seite unter `http://domain.de/bans` erreichbar sein
##### Tutorialautor: [xImSebi](https://github.com/xImSebi)
