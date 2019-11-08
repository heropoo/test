---
layout: post
title:  "Docker容器动态添加端口"
date:   2018-09-16 23:40:33
author: "Heropoo"
categories: 
    - Docker
tags:
    - 虚拟化
    - 容器
    - Docker
excerpt: "给一个已经在运行的docker容器添加端口"
---
突然遇到一个问题怎么给一个已经在运行的docker容器添加端口，找了找资料，记个笔记。

参考：
* [怎么给运行中的docker容器添加新的端口](https://blog.csdn.net/zuoshenglo/article/details/78402772)
* [修改docker容器端口映射的方法](https://blog.csdn.net/wesleyflagon/article/details/78961990)
* [iptable规则查看，添加，删除和修改](https://blog.csdn.net/xfks55/article/details/50148389)

## 方法1 修改iptables端口映射
> docker的端口映射并不是在docker技术中实现的，而是通过宿主机的iptables来实现。通过控制网桥来做端口映射，类似路由器中设置路由端口映射。

比如我们有一个容器的80端口映射到主机的8080端口，先查看iptables到底设置了什么规则：
```sh
sudo iptables -t nat -vnL
```

在结果中有一条：
```
Chain DOCKER
target     prot opt source               destination
RETURN     all  --  0.0.0.0/0            0.0.0.0/0
DNAT       tcp  --  0.0.0.0/0            0.0.0.0/0            tcp dpt:8080 to:172.17.0.3:80
```
我们可以看到docker创建了一个名为DOKCER的自定义的链条Chain。而我开放80端口的容器的ip是172.17.0.3

也可以通过inspect命令查看容器ip
```sh
docker inspect containerId |grep IPAddress
```

我们想再增加一个端口映射，比如`8081->81`，就在这个链条是再加一条规则：
```sh
sudo iptables -t nat -A  DOCKER -p tcp --dport 8081 -j DNAT --to-destination 172.17.0.3:81
```

如果加错了或者想修改：

先显示行号查看
```sh
sudo iptables -t nat -vnL DOCKER --line-number
```

删除规则3
```sh
sudo iptables -t nat -D DOCKER 3
```

## 方法2 修改容器配置文件
容器的配置文件`/var/lib/docker/containers/[containerId]`目录下，`hostconfig.json`和`config.v2.json`
修改好之后，重启容器服务。

## 方法3 把运行中的容器生成新的镜像，然后运行新的镜像

1. 提交一个运行中的容器为镜像
```sh
docker commit containerid heropoo/example
```

2. 运行`heropoo/example`镜像并添加8080映射容器80端口
```sh
docker run -d -p 8000:80  heropoo/example /bin/sh
```

试试吧~😎