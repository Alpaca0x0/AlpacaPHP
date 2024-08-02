FROM nginx

ENV PHP_VERSION=8.3
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && \
	apt-get install -y software-properties-common gnupg2 && \
	apt-key adv --fetch-keys https://packages.sury.org/php/apt.gpg && \
	echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list && \
	#add-apt-repository -y ppa:ondrej/php && \
	apt-get update && \
	apt-get install -y supervisor php$PHP_VERSION-fpm php$PHP_VERSION-mysql php$PHP_VERSION-gd php$PHP_VERSION-mbstring && \
	apt-get purge -y --auto-remove

RUN ln -s /run/php/php$PHP_VERSION-fpm.sock /run/php/php-fpm.sock

COPY ./supervisord.conf /etc/supervisor/supervisord.conf
CMD ["/usr/bin/supervisord"]
