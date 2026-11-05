# Usa a imagem oficial do PHP com servidor Apache embutido
FROM php:8.2-apache

# Instala as extensões necessárias para o PHP conversar com o PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilita o módulo de reescrita de URL do Apache
RUN a2enmod rewrite

# Copia todos os seus arquivos (frontend, backend) para a pasta pública do servidor
COPY ./ /var/www/html/

# Dá as permissões corretas para a pasta
RUN chown -R www-data:www-data /var/www/html/

# Libera a porta 80
EXPOSE 80