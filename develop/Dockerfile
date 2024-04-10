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

RUN wget https://github.com/osTicket/osTicket/archive/refs/heads/develop.zip -O /tmp/osticket.zip \
    && unzip /tmp/osticket.zip -d /var/www/ \
    && mv /var/www/osTicket-develop /var/www/osticket-develop \
    && cp /var/www/osticket-develop/include/ost-sampleconfig.php /var/www/osticket-develop/include/ost-config.php \
    && chown -R www-data:www-data /var/www/osticket-develop \
    && rm /tmp/osticket.zip

RUN service mysql start && \
    mysql -e "CREATE USER 'developer'@'%' IDENTIFIED BY '12345678';" && \
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'developer'@'%' WITH GRANT OPTION;" && \
    mysql -e "CREATE DATABASE osticket_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" && \
    mysql -e "GRANT ALL PRIVILEGES ON osticket_dev.* TO 'developer'@'%';" && \
    mysql -e "FLUSH PRIVILEGES;" 

RUN rm /etc/nginx/sites-enabled/default
ADD nginx/osticket-develop_nginx.conf /etc/nginx/sites-available/osticket-develop.conf
RUN ln -s /etc/nginx/sites-available/osticket-develop.conf /etc/nginx/sites-enabled/

ADD nginx/www.conf /etc/php/8.1/fpm/pool.d/

ADD tools/cron-dev.php /var/www/osticket-develop/api/
ADD tools/test-mailparser.php /var/www/osticket-develop/api/
ADD tools/test-mimeDecode.php /var/www/osticket-develop/api/
RUN chown -R www-data:www-data /var/www/osticket-develop/api/

EXPOSE 80
EXPOSE 22
EXPOSE 3306

CMD service mysql start && service php8.1-fpm start && service nginx start && tail -f /var/log/nginx/access.log /var/log/nginx/error.log;
