FROM php:8.2-apache

# Copy all project files into server
COPY . /var/www/html/

# Expose port
EXPOSE 80