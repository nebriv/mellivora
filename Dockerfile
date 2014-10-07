FROM debian:wheezy
MAINTAINER Kevin Law <kevin@stealsyour.pw>
# Install base packages
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && \
    apt-get -yq install \
    curl \
    sudo \
    git && \
    rm -rf /var/lib/apt/lists/*

# Configure /app folder with sample app
ADD . /app
# Add application code onbuild
ONBUILD RUN chown www-data:www-data /app -R
RUN chmod +x /app/install/install.sh
RUN /app/install/install.sh

VOLUME ["/etc/mysql", "/var/lib/mysql" ]

EXPOSE 80 443 3306
WORKDIR /app


ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# I can't get apache and mysql to run on startup
CMD ["/usr/bin/mysqld_safe"]

# just build the image and run:
# docker run -name mellivora_testing -p 443:443 -p 80:80 -p 3306:3306 -i -t mellivora /bin/bash
# then in the shell, service mysql start && service apache2 start. ctrl+p, then ctrl+q to detach.
