FROM php:8.2-apache-bullseye

WORKDIR /var/www/html
# COPY ./src /var/www/html

# RUN chown www-data:www-data -R /var/www/bootstrap/cache\
# 	&&chown www-data:www-data -R /var/www/storage

# COPY ./config/vhost.conf /etc/apache2/sites-enabled/000-default.conf
# COPY ./config/ports.conf /etc/apache2/ports.conf

RUN apt-get update && apt-get install -y \
	git zip zlib1g zlib1g-dev libzip-dev libpng-dev libmagickwand-dev unzip

RUN docker-php-ext-install gd

# RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql


RUN curl -sLS https://getcomposer.org/installer \
	| php -- --install-dir=/usr/bin/ --filename=composer


# RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
# RUN export NVM_DIR="$HOME/.nvm"\
# 	&& [ -s "$NVM_DIR/nvm.sh" ] \
# 	&& \. "$NVM_DIR/nvm.sh" \
# 	&& [ -s "$NVM_DIR/bash_completion" ] \
# 	&& \. "$NVM_DIR/bash_completion" \
# 	&& nvm install 18 \
# 	&& nvm use 18 && \
# 	node -v

# RUN a2enmod rewrite