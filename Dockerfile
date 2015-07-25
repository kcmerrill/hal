FROM kcmerrill/base

RUN apt-get update && apt-get install -y openssh-server apache2 supervisor telnet
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
EXPOSE 8080
EXPOSE 80
COPY . /var/www

ENTRYPOINT "/usr/bin/supervisord"
