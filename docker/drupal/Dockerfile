FROM php:8.2-apache-bullseye

# COPY ./config/vhost.conf /etc/apache2/sites-enabled/000-default.conf
# COPY ./config/ports.conf /etc/apache2/ports.conf

RUN if command -v a2enmod; then a2enmod rewrite; fi;

RUN apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libjpeg-dev \
		libpng-dev \
		libpq-dev \
		libwebp-dev \
		libzip-dev \
	;

RUN docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg=/usr \
		--with-webp \
	;

RUN docker-php-ext-install -j "$(nproc)" \
		gd \
		opcache \
		pdo_mysql \
		pdo_pgsql \
		zip \
	; 

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini


COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

# https://www.drupal.org/node/3060/release
ENV DRUPAL_VERSION 10.1.5

WORKDIR /opt/drupal
RUN set -eux; \
	export COMPOSER_HOME="$(mktemp -d)"; \
	composer create-project --no-interaction "drupal/recommended-project:$DRUPAL_VERSION" ./; \
	chown -R www-data:www-data web/sites web/modules web/themes; \
	rmdir /var/www/html; \
	ln -sf /opt/drupal/web /var/www/html; \
	# delete composer cache
	rm -rf "$COMPOSER_HOME"

ENV PATH=${PATH}:/opt/drupal/vendor/bin


# RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
# RUN export NVM_DIR="$HOME/.nvm"\
# 	&& [ -s "$NVM_DIR/nvm.sh" ] \
# 	&& \. "$NVM_DIR/nvm.sh" \
# 	&& [ -s "$NVM_DIR/bash_completion" ] \
# 	&& \. "$NVM_DIR/bash_completion" \
# 	&& nvm install 18 \
# 	&& nvm use 18 && \
# 	node -v
