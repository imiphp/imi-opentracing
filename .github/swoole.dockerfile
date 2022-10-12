ARG SWOOLE_DOCKER_VERSION

FROM phpswoole/swoole:${SWOOLE_DOCKER_VERSION}

RUN docker-php-ext-install -j$(nproc) pcntl pdo_mysql

RUN php --ri redis || (pecl install redis && docker-php-ext-enable redis)