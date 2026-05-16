FROM php:8.2-apache

# Enable Apache rewrite module (optional but useful)
RUN a2enmod rewrite

# Copy all project files into Apache web root
COPY . /var/www/html/

# Set the default page to register.html
RUN echo 'DirectoryIndex register.html index.php index.html' > /etc/apache2/conf-available/directoryindex.conf \
    && a2enconf directoryindex