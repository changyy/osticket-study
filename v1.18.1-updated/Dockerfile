FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y \
    nginx \
    php8.1-cli \
    php8.1-imap \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-uuid \
    php8.1-xmlrpc \
    php8.1-http \
    php8.1-raphf \
    php8.1-cgi \
    php8.1-gd \
    php8.1-intl \
    php8.1-apcu \
    php8.1-zip \
    php8.1-xml \
    php8.1-fpm \
    php8.1-mysql \
    vim \
    wget \
    unzip \
    mysql-server \
    && rm -rf /var/lib/apt/lists/*

ADD nginx/www.conf /etc/php/8.1/fpm/pool.d/
RUN rm /etc/nginx/sites-enabled/default

# v1.18.1 branch

RUN wget https://github.com/osTicket/osTicket/releases/download/v1.18.1/osTicket-v1.18.1.zip -O /tmp/osticket-v1.18.1.zip \
    && unzip /tmp/osticket-v1.18.1.zip -d /var/www/ \
    && mv /var/www/upload /var/www/osticket-v1.18.1 \
    && cp /var/www/osticket-v1.18.1/include/ost-sampleconfig.php /var/www/osticket-v1.18.1/include/ost-config.php \
    && chown -R www-data:www-data /var/www/osticket-v1.18.1 \
    && rm /tmp/osticket-v1.18.1.zip

RUN wget https://s3.amazonaws.com/downloads.osticket.com/lang/1.18.x/zh_TW.phar -O /var/www/osticket-v1.18.1/include/i18n/zh_TW.phar \
    && wget https://s3.amazonaws.com/downloads.osticket.com/lang/1.18.x/en_GB.phar -O /var/www/osticket-v1.18.1/include/i18n/en_GB.phar 

ADD tools/cron-dev.php /var/www/osticket-v1.18.1/api/
ADD tools/test-mailparser.php /var/www/osticket-v1.18.1/api/
ADD tools/test-mimeDecode.php /var/www/osticket-v1.18.1/api/
RUN chown -R www-data:www-data /var/www/osticket-v1.18.1/api/

ADD resource/mimeDecode.php /var/www/osticket-v1.18.1/include/pear/Mail/mimeDecode-v2015.php
RUN mv /var/www/osticket-v1.18.1/include/pear/Mail/mimeDecode.php /var/www/osticket-v1.18.1/include/pear/Mail/mimeDecode-v2006.php
RUN ln -s /var/www/osticket-v1.18.1/include/pear/Mail/mimeDecode-v2015.php /var/www/osticket-v1.18.1/include/pear/Mail/mimeDecode.php

ADD nginx/osticket-v1.18.1_nginx.conf /etc/nginx/sites-available/osticket-v1.18.1.conf
RUN ln -s /etc/nginx/sites-available/osticket-v1.18.1.conf /etc/nginx/sites-enabled/

# develop branch

RUN wget https://github.com/osTicket/osTicket/archive/refs/heads/develop.zip -O /tmp/osticket-develop.zip \
    && unzip /tmp/osticket-develop.zip -d /var/www/ \
    && mv /var/www/osTicket-develop /var/www/osticket-develop \
    && cp /var/www/osticket-develop/include/ost-sampleconfig.php /var/www/osticket-develop/include/ost-config.php \
    && chown -R www-data:www-data /var/www/osticket-develop \
    && rm /tmp/osticket-develop.zip

ADD tools/cron-dev.php /var/www/osticket-develop/api/
ADD tools/test-mailparser.php /var/www/osticket-develop/api/
ADD tools/test-mimeDecode.php /var/www/osticket-develop/api/
RUN chown -R www-data:www-data /var/www/osticket-develop/api/

ADD resource/mimeDecode.php /var/www/osticket-develop/include/pear/Mail/mimeDecode-v2015.php
RUN mv /var/www/osticket-develop/include/pear/Mail/mimeDecode.php /var/www/osticket-develop/include/pear/Mail/mimeDecode-v2006.php
RUN ln -s /var/www/osticket-develop/include/pear/Mail/mimeDecode-v2015.php /var/www/osticket-develop/include/pear/Mail/mimeDecode.php

ADD nginx/osticket-develop_nginx.conf /etc/nginx/sites-available/osticket-develop.conf

# db server

RUN service mysql start && \
    mysql -e "CREATE USER 'developer'@'%' IDENTIFIED BY '12345678';" && \
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'developer'@'%' WITH GRANT OPTION;" && \
    mysql -e "CREATE DATABASE osticket_v1_18_1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    mysql -e "GRANT ALL PRIVILEGES ON osticket_v1_18_1.* TO 'developer'@'%';" && \
    mysql -e "GRANT ALL PRIVILEGES ON osticket_dev.* TO 'developer'@'%';" && \
    mysql -e "FLUSH PRIVILEGES;" 

EXPOSE 80
EXPOSE 22
EXPOSE 3306

CMD service mysql start && service php8.1-fpm start && service nginx start && tail -f /var/log/nginx/access.log /var/log/nginx/error.log;
