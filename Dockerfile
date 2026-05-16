FROM php:8.2-apache

# Copy application files
COPY . /var/www/html/

# Fix the common Apache MPM conflict
RUN a2dismod mpm_event mpm_worker || true && \
    a2enmod mpm_prefork rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Start Apache
CMD ["apache2-foreground"]