FROM php:8.1-cli
WORKDIR "/var/www"

ENV DEBIAN_FRONTEND noninteractive

RUN useradd -m apiuser -u 1000 -G www-data

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
	unzip \
    git \
	&& apt-get clean -y \
	&& rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN yes | pecl install xdebug

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && chmod +x composer.phar \
    && mv composer.phar /usr/local/bin/composer

USER apiuser

RUN composer global require friendsofphp/php-cs-fixer