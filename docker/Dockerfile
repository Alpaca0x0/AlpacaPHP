FROM nginx

ENV PHP_VERSION=8.4
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY php/extensions.txt /tmp/php-extensions.txt
COPY entrypoint.d/php-fpm.sh /docker-entrypoint.d/php-fpm.sh

RUN apt-get update && \
	apt-get install -y software-properties-common gnupg2 && \
	apt-key adv --fetch-keys https://packages.sury.org/php/apt.gpg && \
	echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list && \
	apt-get update && \
	PHP_EXT_PACKAGES="$(grep -Ev '^\s*($|#)' /tmp/php-extensions.txt | sed "s|^|php$PHP_VERSION-|" | tr '\n' ' ')" && \
	apt-get install -y php$PHP_VERSION-fpm $PHP_EXT_PACKAGES && \
	apt-get purge -y --auto-remove

COPY php/php.ini /etc/php/$PHP_VERSION/fpm/php.ini

RUN ln -s /run/php/php$PHP_VERSION-fpm.sock /run/php/php-fpm.sock

RUN chmod +x /docker-entrypoint.d/*.sh
