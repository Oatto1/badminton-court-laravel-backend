FROM ubuntu:24.04

LABEL maintainer="Oatto1"

ARG WWWGROUP=1000
ARG NODE_VERSION=22

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

# -----------------------------
# Timezone
# -----------------------------
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# -----------------------------
# Base Packages
# -----------------------------
RUN apt-get update && apt-get install -y \
    gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 \
    libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano \
    software-properties-common \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# PHP Repo (Ondrej)
# -----------------------------
RUN mkdir -p /etc/apt/keyrings \
    && curl -sS https://keyserver.ubuntu.com/pks/lookup?op=get\&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c \
    | gpg --dearmor -o /etc/apt/keyrings/ppa_ondrej_php.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] http://ppa.launchpad.net/ondrej/php/ubuntu noble main" \
    > /etc/apt/sources.list.d/ppa_ondrej_php.list

# -----------------------------
# Install PHP + Extensions
# -----------------------------
RUN apt-get update && apt-get install -y \
    php8.4-cli \
    php8.4-pgsql \
    php8.4-sqlite3 \
    php8.4-gd \
    php8.4-curl \
    php8.4-imap \
    php8.4-mysql \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-zip \
    php8.4-bcmath \
    php8.4-soap \
    php8.4-intl \
    php8.4-readline \
    php8.4-ldap \
    php8.4-msgpack \
    php8.4-igbinary \
    php8.4-redis \
    php8.4-swoole \
    php8.4-memcached \
    php8.4-pcov \
    php8.4-xdebug \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Composer
# -----------------------------
RUN curl -sLS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/bin/ --filename=composer

# -----------------------------
# NodeJS
# -----------------------------
RUN mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
    | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_VERSION}.x nodistro main" \
    > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y nodejs \
    && npm install -g npm \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Allow PHP bind port 80
# -----------------------------
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

# -----------------------------
# User (Sail Compatible)
# -----------------------------
RUN groupadd --force -g ${WWWGROUP} sail \
    && useradd -ms /bin/bash --no-user-group -g ${WWWGROUP} -u 1337 sail

# -----------------------------
# Copy Configs
# -----------------------------
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini

RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]
