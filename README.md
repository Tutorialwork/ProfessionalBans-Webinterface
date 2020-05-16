# ProfessionalBans-Webinterface v3
Webinterface for my minecraft plugin ProfessionalBans Reloaded

**THIS IS A BETA VERSION AND NOT STABLE!**

# How to setup the new webinterface?
- [Download all files](https://github.com/Tutorialwork/ProfessionalBans-Webinterface/archive/v3.zip)
-  Move the files to your webserver
-  Setting up your Apache2 server for the new webinterface. Open the Apache2 settings file with `nano /etc/apache2/sites-available/000-default.conf` and change the `DocumentRoot` to this:

```
<VirtualHost *:80>
        ServerName professionalbans.yourdomain.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/your/path/to/professionalbans/public #This line should end with /public. Because when this is not you EXPOSING your MySQL credentials.

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

- Go to your directory of your web server for example `cd  /var/www/your/path/to/professionalbans`
- Install composer `apt install composer`
- Install Apache2 pack `composer require apache-pack`
- Visit your website you will be redirected to a new installer.

# REST API

ProfessionalBans is developer friendly. You can use the REST API to access all data from the webinterface over the api.

How to use?

- You can access the api with `/api/login` for example, a full documentation is coming soon.
- All what you need is your API key