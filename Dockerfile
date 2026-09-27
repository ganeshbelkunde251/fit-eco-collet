# Production Dockerfile for EcoCollect on Google Cloud Run
FROM php:8.2-apache

# Install required PHP extensions (PDO MySQL, cURL)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql curl

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy application files to web server root
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to listen on Google Cloud Run $PORT (default 8080)
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 8080

# Start Apache in foreground
CMD ["apache2-foreground"]
