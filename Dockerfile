ARG PHP_EXTENSIONS="apcu bcmath pdo_mysql pdo_pgsql pgsql imagick gd igbinary redis intl"
FROM thecodingmachine/php:7.4-v4-slim-apache as php_base
ENV TEMPLATE_PHP_INI=production
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY --chown=docker:docker . /var/www/html
RUN composer install --quiet --optimize-autoloader --no-dev

# Uncomment following lines to enable FE asset compiling on the deployment

#FROM node:10 as node_dependencies
#WORKDIR /var/www/html
#ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=false
#COPY --from=php_base /var/www/html /var/www/html
#RUN npm set progress=false && \
#    npm config set depth 0 && \
#    npm install && \
#    npm run prod && \
#    rm -rf node_modules
#FROM php_base
#COPY --from=node_dependencies --chown=docker:docker /var/www/html /var/www/html
