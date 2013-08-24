Miranda
=======

Introduction
------------
Gestionnaire de la compagnie Miranda

Installation
------------

VHost Apache
```apache
<VirtualHost *:80>
    ServerName manager.compagniemiranda.com
    ServerAdmin webmaster@localhost

    DocumentRoot /home/miranda/www/public
    SetEnv APPLICATION_ENV "prod"
    <Directory /home//miranda/www/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```apache
