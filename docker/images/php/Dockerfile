FROM php:8.2.6-fpm

RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list \
    && apt-get update && apt-get install -y unzip vim git libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev \
      libzip-dev libpq-dev libicu-dev symfony-cli librabbitmq-dev supervisor \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug-3.2.1 \
    && docker-php-ext-enable xdebug \
    && pecl install amqp-1.11.0 \
    && docker-php-ext-enable amqp \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql pdo_pgsql \
    && docker-php-ext-configure intl && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

COPY ./docker/configs/php /usr/local/etc/php/conf.d

RUN mkdir -p /opt/home && chmod 777 /opt/home
ENV HOME /opt/home

ARG RABBITMQ_QUEUE_NAME
ARG MESSENGER_WORKER_PATH=/etc/supervisor/conf.d/messenger-worker.conf

RUN touch $MESSENGER_WORKER_PATH && \
    echo "[program:messenger-consume]" >> $MESSENGER_WORKER_PATH && \
    echo "command=/bin/bash -c 'source /var/www/symfony/.env && php /var/www/symfony/bin/console messenger:consume async --queues=$RABBITMQ_QUEUE_NAME --time-limit=3600'" >> $MESSENGER_WORKER_PATH && \
    echo "user=root" >> $MESSENGER_WORKER_PATH && \
    echo "numprocs=2" >> $MESSENGER_WORKER_PATH && \
    echo "autostart=true" >> $MESSENGER_WORKER_PATH && \
    echo "autorestart=true" >> $MESSENGER_WORKER_PATH && \
    echo "startsecs=0" >> $MESSENGER_WORKER_PATH && \
    echo "startretries=10" >> $MESSENGER_WORKER_PATH && \
    echo "process_name=%(program_name)s_%(process_num)02d" >> $MESSENGER_WORKER_PATH 

ARG PROJECTIONIST_SLEEP
ARG PROJECTIONIST_WORKER_PATH=/etc/supervisor/conf.d/projectionist-worker.conf

RUN touch $PROJECTIONIST_WORKER_PATH && \
    echo "[program:event-consume]" >> $PROJECTIONIST_WORKER_PATH && \
    echo "command=/bin/bash -c 'source /var/www/symfony/.env && php /var/www/symfony/bin/console event_stream:consume $PROJECTIONIST_SLEEP'" >> $PROJECTIONIST_WORKER_PATH && \
    echo "user=root" >> $PROJECTIONIST_WORKER_PATH && \
    echo "numprocs=1" >> $PROJECTIONIST_WORKER_PATH && \
    echo "autostart=true" >> $PROJECTIONIST_WORKER_PATH && \
    echo "autorestart=true" >> $PROJECTIONIST_WORKER_PATH && \
    echo "startsecs=0" >> $PROJECTIONIST_WORKER_PATH && \
    echo "startretries=10" >> $PROJECTIONIST_WORKER_PATH && \
    echo "process_name=%(program_name)s_%(process_num)02d" >> $PROJECTIONIST_WORKER_PATH 

CMD ["sh", "-c", "service supervisor stop && service supervisor start && php-fpm"]