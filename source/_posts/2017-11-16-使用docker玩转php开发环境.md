---
layout: post
title:  "使用docker玩转php开发环境"
date:   2017-11-16 15:25:01
author: "Heropoo"
categories: 
    - Docker
    - PHP
tags:
    - 虚拟化
    - 容器
    - Docker
    - PHP
excerpt: "还是忙里偷闲研究了下怎么用docker这个东东来整php开发。"
---
还是忙里偷闲研究了下怎么用docker这个东东来整php开发。做点笔记😁

先看`Dockerfile`:
```
# 使用基于alpine linux 的镜像，体积小，下载快
FROM php:7.0-cli-alpine

# 使用中科大的源加快下载速度
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories

# 安装下基本的php扩展
RUN docker-php-ext-install pdo pdo_mysql mysqli
```

#### 构建
```sh
docker build -t heropoo/php-cli-alpine .  #先切换到你的Dockerfile目录
```

#### 运行
```sh
docker run -it --rm --name php-cli-server -p 8080:80  -v //c/Users/ttt/www:/usr/src/www -w /usr/src/www php:7.0-cli-alpine php -S 0.0.0.0:80
```
这句好长啊，解释下各个参数：
>-t在新容器内指定一个伪终端或终端
>
>-i	允许你对容器内的标准输入 (STDIN) 
>
>-p 映射宿主机端口到容器的端口，上面就是宿主机的8080到容器的80
>
>-v 挂载宿主机目录到容器的目录，上面就是宿主机/c/Users/ttt/www到容器/usr/src/www	
>
>-w	设置工作目录
>
>--rm 容器运行完之后删除
>
>--name	给容器名字
>
>php -S 0.0.0.0:80 就是用php内置的服务器启动一个web服务了，简单点来 哈哈 

我系统是windows，我在浏览器访问docker的web服务：`http://ip:8080`,ip是你的docker的ip,就可以了。

是不是很好玩😜