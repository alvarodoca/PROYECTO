FROM php:8.2-fpm-alpine3.18

# 1. Instalar dependencias esenciales (añadido sqlite)
RUN apk add --no-cache \
    nginx \
    aws-cli \
    sudo \
    sqlite \
    && apk add --no-cache --virtual .build-deps \
    wget \
    unzip

# 2. Instalar Terraform en /usr/bin
RUN wget -q https://releases.hashicorp.com/terraform/1.5.7/terraform_1.5.7_linux_amd64.zip \
    && unzip terraform_1.5.7_linux_amd64.zip -d /usr/bin \
    && rm terraform_1.5.7_linux_amd64.zip \
    && chmod 0755 /usr/bin/terraform

# 3. Configuración del entorno (añadido directorio para DB)
RUN mkdir -p /var/www/html/proyecto_ftp \
    /var/www/terraform \
    /var/www/.aws \
    /var/www/db \
    && adduser www-data root \
    && chown -R www-data:www-data /var/www \
    && echo "www-data ALL=(ALL) NOPASSWD: /usr/bin/terraform" >> /etc/sudoers \
    && echo "extension=pdo_sqlite.so" > /usr/local/etc/php/conf.d/pdo_sqlite.ini \
    && echo "extension=sqlite3.so" >> /usr/local/etc/php/conf.d/sqlite3.ini

# 4. Variables de entorno para la aplicación
ENV DB_PATH=/var/www/db/logins.db \
    TF_IN_AUTOMATION=true

# 5. Copiar archivos de la aplicación
COPY --chown=www-data:www-data ./frontend /var/www/html/proyecto_ftp
COPY --chown=www-data:www-data ./backend /var/www/terraform
COPY --chown=www-data:www-data ./aws-config/credentials /var/www/.aws/
COPY --chown=www-data:www-data ./config /var/www/config

# 6. Configuración de Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# 7. Configuración del workspace
WORKDIR /var/www/terraform

# 8. Puerto expuesto
EXPOSE 8080

# 9. Comando de inicio optimizado (añadida creación de DB)
CMD ["sh", "-c", " \
    touch /var/www/db/logins.db && \
    chown www-data:www-data /var/www/db/logins.db && \
    chmod 664 /var/www/db/logins.db && \
    chown -R www-data:www-data /var/www && \
    php-fpm -D && \
    exec nginx -g 'daemon off;' \
"]
