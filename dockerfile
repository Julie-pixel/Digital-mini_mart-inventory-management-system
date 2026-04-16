FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache modules
RUN a2enmod rewrite && \
    a2enmod dir && \
    a2enmod php8.2

# Enable .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy all project files into server
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 644 /var/www/html/.htaccess

# Restart Apache to apply changes
RUN service apache2 restart || true

# Expose port
EXPOSE 80