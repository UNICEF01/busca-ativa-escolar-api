FROM nginx:1.14.0

ADD vhost.conf /etc/nginx/conf.d/default.conf