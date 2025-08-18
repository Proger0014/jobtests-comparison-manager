FROM yiisoftware/yii2-php:7.4-apache

WORKDIR /app

COPY . .

RUN sed -i -e 's|/app/web|/app/src/web/web|g' /etc/apache2/sites-available/000-default.conf