FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Set proper Apache DirectoryIndex
RUN echo "DirectoryIndex index.php index.html" > /var/www/html/.htaccess

# Copy all project files into server
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80