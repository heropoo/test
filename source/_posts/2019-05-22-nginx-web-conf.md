---
layout: post
title:  "常用nginx web配置"
date:   2019-05-22 18:35:30
author: "Heropoo"
categories: 
    - nginx
tags:
    - nginx
excerpt: "常用nginx对于web项目配置整理"
---
常用nginx对于web项目配置整理，做个笔记。

## php web项目配置
```
server {
  listen 80;
  listen [::]:80;

  # 设置上传最大为5MB
  client_max_body_size 5m;

  root /srv/www/wechat/public;

  index index.html index.php;

  server_name example.com;

  location / {
    try_files $uri $uri/ =404;
  }

  # 支持php
  location ~ \.php$ {
    # Check that the PHP script exists before passing it
    try_files $fastcgi_script_name =404;

    fastcgi_index index.php;
    include fastcgi.conf;

    # With php-fpm (or other unix sockets):
    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
    #  # With php-cgi (or other tcp sockets):
    #  fastcgi_pass 127.0.0.1:9000;
  }

  # deny access to .htaccess files, if Apache's document root
  # concurs with nginx's one
  #
  location ~ /\.ht {
    deny all;
  }
}
```

## php web项目配置 支持laravel、symfony、Yii2单入口
```
...
  location / {
    # First attempt to serve request as file, then
    # as directory, then fall back to displaying a 404.
    #try_files $uri $uri/ =404;  # 注释上面这句，使用下面这句
    try_files $uri $uri/ /index.php?$query_string;
  }
...
```

## php web项目配置 支持ThinkPHP
```
...
  location ~ \.php$ {
    # regex to split $uri to $fastcgi_script_name and $fastcgi_path
    fastcgi_split_path_info ^(.+\.php)(/.+)$;

    # Check that the PHP script exists before passing it
    try_files $fastcgi_script_name =404;

    # Bypass the fact that try_files resets $fastcgi_path_info
    # see: http://trac.nginx.org/nginx/ticket/321
    set $path_info $fastcgi_path_info;
    fastcgi_param PATH_INFO $path_info;   # ThinkPHP依赖PATH_INFO这个环境变量

    fastcgi_index index.php;
    include fastcgi.conf;

    # With php-fpm (or other unix sockets):
    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
    #  # With php-cgi (or other tcp sockets):
    #  fastcgi_pass 127.0.0.1:9000;
  }
...
```

## php web项目配置 禁止访问上传目录下的php文件
```
...
  # 这个块location要放在 location ~ \.php$ 之前
  location ~ ^/uploads/.*\.php$ {     # 所有/uploads文件目录下的.php文件都被禁止访问
    #deny all;  # 返回403
    return 404; #返回404
  }
...
```









