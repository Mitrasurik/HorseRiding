FROM php:8.1-apache

# Installation de Git
RUN apt-get update && \
    apt-get install -y git

RUN curl -L -o php.tar.gz https://www.php.net/distributions/php-8.1.2.tar.gz
RUN tar -xf php.tar.gz && rm php.tar.gz
RUN cd php-8.1.2 \
RUN ./configure \
    --build=x86_64-linux-gnu \
    --with-config-file-path=/usr/local/etc/php \
    --with-config-file-scan-dir=/usr/local/etc/php/conf.d \
    --enable-option-checking=fatal \
    --with-mhash \
    --with-pic \
    --enable-ftp \
    --enable-mbstring \
    --enable-mysqlnd \
    --with-password-argon2 \
    --with-sodium=shared \
    --with-pdo-sqlite=/usr \
    --with-sqlite3=/usr \
    --with-curl \
    --with-iconv \
    --with-openssl \
    --with-readline \
    --with-zlib \
    --disable-phpdbg \
    --with-pear \
    --with-libdir=lib/x86_64-linux-gnu \
    --disable-cgi \
    --with-apxs2 \
    --with-pdo-mysql \
    build_alias=x86_64-linux-gnu

# Installation de Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    php composer-setup.php \
    php -r "unlink('composer-setup.php');"

# Installation de PDO
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli \
    && docker-php-ext-enable pdo_mysql mysqli

# Configuration de l'utilisateur et du répertoire de travail
WORKDIR /var/www/html
RUN usermod -u 1000 www-data

# Copie des fichiers de l'application
COPY . /var/www/html

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && apt install symfony-cli
# Exposition du port 9000 pour le serveur PHP-FPM
EXPOSE 8000

# Commande par défaut pour démarrer le serveur PHP-FPM
CMD ["symfony", "server:start"]