# ProfessionalBans-Webinterface v3
Webinterface for my minecraft plugin ProfessionalBans Reloaded

**THIS IS A BETA VERSION AND NOT STABLE!**

# How to setup the new webinterface?
1. Download all files
2. Move the files to your webserver
3. Setting up your Apache2 server for the new webinterface. Open the Apache2 settings file with `nano /etc/apache2/sites-available/000-default.conf` and change the `DocumentRoot` to this:

```
<VirtualHost *:80>
        ServerName professionalbans.yourdomain.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/your/path/to/professionalbans/public #This line should end with /public. Because when this is not you EXPOSING your MySQL credentials.

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

4. Open the .env file and setting up your MySQL database credentials under `DATABASE_URL`
5. You can change the language in `/config/packages/translation.yaml` by setting `default_locale` to German (de) or English (en)
