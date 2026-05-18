FROM php:8.2-apache

# Install and enable the mysqli extension required by your connection script
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy all of your PHP files into the Apache web directory
COPY . /var/www/html/

# Tell Render to route web traffic to port 80
EXPOSE 80