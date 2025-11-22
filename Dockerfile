# Use official WordPress image with PHP 8.1
FROM wordpress:latest

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for mPDF
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mbstring \
    zip \
    intl \
    dom \
    xml \
    pdo \
    pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Verify Composer installation
RUN composer --version

# Set working directory
WORKDIR /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Use the default WordPress entrypoint
CMD ["apache2-foreground"]
