FROM ubuntu
RUN apt-get update 
RUN apt update
RUN apt install sudo -y
RUN apt install curl -y
RUN curl -sL https://deb.nodesource.com/setup_16.x | sudo -E bash -
RUN apt-get install -y nodejs
RUN apt-get install -y --no-install-recommends firefox
RUN apt-get install -y firefox-geckodriver
RUN apt install xvfb apache2 -yq
RUN apt install php libapache2-mod-php php-curl -y
RUN apt-get clean
RUN a2enmod headers
RUN useradd -rm -p $(openssl passwd -1 2020) -d /home/ubuntu -s /bin/bash -g root -G sudo -u 1001 ubuntu
RUN ln -s /usr/bin/geckodriver
RUN chmod 777 /usr/bin/geckodriver
RUN mkdir -m777 /var/log/web
RUN mkdir -m777 /var/www/html/temp
COPY apache2.conf /etc/apache2/apache2.conf
COPY config_test.php /var/www/html/config.php
COPY php_test.ini /etc/php/7.4/apache2/php.ini
#COPY . /var/www/html/
#CMD ["service", "apache2", "start"]