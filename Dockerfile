# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libsqlite3-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip mbstring

# 1. Enable Apache Rewrite Module (Required for routing)
RUN a2enmod rewrite

# 2. Fix AH00558 Warning (Set ServerName)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 3. Configure Apache Ports
# We overwrite ports.conf to listen on 5005
RUN echo "Listen 5005" > /etc/apache2/ports.conf

# 4. Configure VirtualHost explicitly
# This is the most important part: 'AllowOverride All' enables the .htaccess file
RUN echo '<VirtualHost *:5005>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Create necessary directories
RUN mkdir -p database public/uploads

# Copy Entrypoint Script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose Port 5005
EXPOSE 5005

# Set Entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default Command
CMD ["apache2-foreground"]