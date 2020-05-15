# ProfessionalBans-Webinterface v3
Webinterface for my minecraft plugin ProfessionalBans Reloaded

**THIS IS A BETA VERSION AND NOT STABLE!**

# How to setup the new webinterface?
1. [Download all files](https://github.com/Tutorialwork/ProfessionalBans-Webinterface/archive/v3.zip)
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

4. Open the `.env` file and setting up your MySQL database credentials under `DATABASE_URL`
5. Install composer `apt install composer`
6. Install Apache2 pack `composer require apache-pack`

# REST API

ProfessionalBans is developer friendly. You can use the REST API to access all data from the webinterface over the api.

How to use?

- You can access the api with `/api/login` for example, a full documentation is coming soon.
- All what you need is your API key