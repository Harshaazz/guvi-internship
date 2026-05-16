FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql && \
    docker-php-ext-enable mysqli pdo_mysql

# Copy project files
COPY . /var/www/html/

# Fix Apache MPM + DirectoryIndex + Rewrite
RUN a2dismod mpm_event mpm_worker || true && \
    a2enmod mpm_prefork rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    echo "DirectoryIndex register.html" >> /etc/apache2/apache2.conf

# Start Apache
CMD ["apache2-foreground"]