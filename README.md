Miranda
=======

Introduction
------------
Gestionnaire de la compagnie Miranda

Installation
------------

VHost Apache :
```apache
<VirtualHost *:80>
    ServerName manager.compagniemiranda.com
    ServerAdmin webmaster@localhost

    DocumentRoot /home/miranda/www/public
    SetEnv APPLICATION_ENV "dev"

    AddDefaultCharset UTF-8

    <IfModule mod_php5.c>
        php_flag file_uploads On
        php_value post_max_size "10M"
        php_value upload_max_filesize "10M"
        php_flag session.upload_progress.enabled On
        php_value session.upload_progress.freq "1%"
        php_value session.upload_progress.min_freq "1"
    </IfModule>

    <Location />
        RewriteEngine On
        # The following rule tells Apache that if the requested filename
        # exists, simply serve it.
        RewriteCond %{REQUEST_FILENAME} -s [OR]
        RewriteCond %{REQUEST_FILENAME} -l [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^.*$ - [NC,L]
        # The following rewrites all other queries to index.php. The
        # condition ensures that if you are using Apache aliases to do
        # mass virtual hosting, the base path will be prepended to
        # allow proper resolution of the index.php file; it will work
        # in non-aliased environments as well, providing a safe, one-size
        # fits all solution.
        RewriteCond %{REQUEST_URI}::$1 ^(/.+)(.+)::\2$
        RewriteRule ^(.*) - [E=BASE:%1]
        RewriteRule ^(.*)$ %{ENV:BASE}index.php [NC,L]
    </Location>

    <Directory /home/miranda/www/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
        Order allow,deny
        Allow from all
        Require all granted
    </Directory>

    Alias /pictures /home/miranda/data/pictures
    <Directory /home/miranda/data/pictures>
        Options FollowSymLinks
        AllowOverride None
        Order allow,deny
        Allow from all
        Require all granted
    </Directory>
</VirtualHost>
```
