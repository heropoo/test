---
layout: post
title:  "容器的宠儿AlpineLinux的基本使用"
date:   2019-01-17 14:48:41
author: "heropoo"
categories: 
    - Linux
tags: 
    - Linux
    - AlpineLinux

excerpt: "容器的宠儿AlpineLinux的基本使用"
---
现在docker的容器技术很流行，对于我们这种整天被各种万恶的开发环境坑的开发来说是个解放。但是当我们在拉取docker镜像的时候，会发现普遍基于debian或者ubuntu的镜像都是体积很大，动辄几十或者上百兆。当然了docker镜像在构建的时候会删除旭东无用的东西并且使用精简系统模板。尽管这样做还是很大，起码也得有个几十兆吧。所以使用一个本身体积就小的操作系统做基础模板来构建才是关键。AlpineLinux是一个使用busybox的linux操作系统，而他的体积只需要4兆多。所以对我这种喜欢轻量级的人还说，这真是好东西啊！

哈哈~废话不多说了，说说这个系统的基本使用吧

## 软件包管理

更新软件包索引文件
```
apk update
```

如果感觉网速很慢，那可以先换个中国的源
```
#中科大的源 速度杠杠的
sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories
```

下面我们以安装nginx为例

安装软件包
```
apk add nginx
```

安装软件包到最新
```
apk add --update nginx
```

查找软件包
```
apk search nginx 
```

卸载软件包
```
apk del nginx
```

## 服务管理

启动Nginx
```
/etc/init.d/nginx start
```

添加nginx到启动服务中，下次开机自动运行
```
rc-update add nginx
```

把nginx从启动服务中移除，下次开机不会自动运行
```
rc-update del nginx
```

## 一些网络工具所在软件包
```
telnet  =>  busybox-extras
```  