FROM webdevops/php-nginx:latest
RUN  apt-get update -y && apt-get install -y supervisor
RUN  rm -f /opt/docker/etc/nginx/vhost.conf
RUN mkdir -p /usr/share/nginx/html/office
COPY ./backend /usr/share/nginx/html/office/backend
COPY ./common /usr/share/nginx/html/office/common
COPY ./console /usr/share/nginx/html/office/console
COPY ./docker /usr/share/nginx/html/office/docker
COPY ./environments /usr/share/nginx/html/office/environments
COPY ./frontend /usr/share/nginx/html/office/frontend
COPY ./vendor /usr/share/nginx/html/office/vendor
COPY ./.bowerrc /usr/share/nginx/html/office/
COPY ./.htaccess /usr/share/nginx/html/office/
COPY ./.idea /usr/share/nginx/html/office/
COPY ./LICENSE.md /usr/share/nginx/html/office/
COPY ./README.md /usr/share/nginx/html/office/
COPY ./codeception.yml /usr/share/nginx/html/office/
COPY ./init /usr/share/nginx/html/office/
COPY ./init.bat /usr/share/nginx/html/office/
COPY ./requirements.php /usr/share/nginx/html/office/
COPY ./yii /usr/share/nginx/html/office/
COPY ./yii.bat /usr/share/nginx/html/office/
COPY ./yii_test /usr/share/nginx/html/office/
COPY ./yii_test.bat /usr/share/nginx/html/office/
RUN mkdir -p /etc/ssl/certs/nginx/
RUN cp /usr/share/nginx/html/office/docker/korzhov-office-kharkiv-ua.key /etc/ssl/certs/nginx/korzhov-office-kharkiv-ua.key 
RUN cp /usr/share/nginx/html/office/docker/korzhov-office-kharkiv-ua.crt /etc/ssl/certs/nginx/korzhov-office-kharkiv-ua.crt 
RUN cp /usr/share/nginx/html/office/docker/supervisord.conf /etc/supervisord.conf
RUN cp /usr/share/nginx/html/office/docker/office.conf /opt/docker/etc/nginx/vhost.conf
RUN cp /usr/share/nginx/html/office/docker/main-local.php /usr/share/nginx/html/office/common/config/main-local.php
RUN cp /usr/share/nginx/html/office/docker/index_f.php /usr/share/nginx/html/office/frontend/views/site/index.php 
RUN cp /usr/share/nginx/html/office/docker/index_b.php /usr/share/nginx/html/office/backend/views/site/index.php 
RUN chmod -R 777 /usr/share/nginx/html/office
RUN service nginx restart
EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
