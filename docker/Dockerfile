FROM ubuntu:18.04
LABEL maintainer="Andrej.Sajenko@gmail.com"

VOLUME ["/var/moodledata"]
EXPOSE 80 443

# Let the container know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

ADD ./foreground.sh /etc/apache2/foreground.sh

RUN apt-get update && \
    apt-get -y install \
    php7.2-curl \
    curl git unzip apache2 php \
    php-gd libapache2-mod-php wget \
    php-pgsql curl libcurl4 php-curl \
    php-xmlrpc php-intl php-mysql \
    git-core php-xml php-mbstring \
    php-zip php-soap cron

RUN chown -R www-data:www-data /var/www/html && \
    chmod +x /etc/apache2/foreground.sh

# Enable SSL, moodle requires it
RUN a2enmod ssl && a2ensite default-ssl  #if using proxy dont need actually secure connection

# Cleanup, this is ran to reduce the resulting size of the image.
RUN apt-get clean autoclean && apt-get autoremove -y && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/lib/dpkg/* /var/lib/cache/* /var/lib/log/*

CMD ["/etc/apache2/foreground.sh"]