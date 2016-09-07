FROM php:7.0-apache
MAINTAINER Yoan Blanc <yoan@dosimple.ch>

RUN a2enmod rewrite
RUN echo "Options +Indexes" > /var/www/html/.htaccess \
 && echo "AddDefaultCharset UTF-8" >> /var/www/html/.htaccess \
 && echo "<?php phpinfo();" >> /var/www/html/info.php
