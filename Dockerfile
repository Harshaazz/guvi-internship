FROM php:8.2-apache

# Copy project files to Apache web root
COPY . /var/www/html/

# Make register.html the default page
RUN printf '<IfModule dir_module>\nDirectoryIndex register.html index.php index.html\n</IfModule>\n' > /etc/apache2/conf-available/custom-directoryindex.conf \
    && a2enconf custom-directoryindex

EXPOSE 80