FROM php:8.3-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev libzip-dev libonig-dev libxml2-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev default-mysql-client unzip git curl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j2 bcmath intl mbstring pdo_mysql zip gd pcntl soap \
 && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY prometheus/ /var/www/html/
COPY dev/ /opt/promethee/
RUN php /opt/promethee/restore-vendor.php \
 && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app bootstrap/cache \
 && chmod +x /opt/promethee/entrypoint.sh
ENTRYPOINT ["/opt/promethee/entrypoint.sh"]
