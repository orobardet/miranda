Miranda
=======

Introduction
------------
Gestionnaire de la compagnie Miranda

Pré-requis
----------

- webserver avec url rewriting
- php5 >= 5.6
- php5-intl
- php5-xcache
- php5-mysql
- sendmail fonctionnel
- une base de donnée MySQL
- git
- connexion internet
  

Pour développement :
- Less CSS (npm install -g less)


Installation
------------

```shell
git clone https://github.com/orobardet/miranda.git
cd miranda
./install.sh
```

VHost Apache (peut-être généré par l'installeur) :
```apache
<VirtualHost *:80>
    ServerName HOST
    ServerAdmin webmaster@localhost

    DocumentRoot /home/miranda/www/public
    # Uncomment if running in development environment (application will provide debugging features)
    # SetEnv APPLICATION_ENV "dev"

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


Mise à jour de Font-awesome
---------------------------

- Remplacer les fichiers de font dans `public/font`
- Remplacer les fichiers less de Fontawesome dans `public/css/fontawesome`
- Editer `public/css/fontawesome/variables.less`, et retirer le `s` à la fin de la variable `@fa-font-path`