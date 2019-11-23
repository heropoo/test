---
layout: post
title:  "EFI模式下安装win10+Archlinux"
date:   2017-09-13 22:49:50
author: "Heropoo"
categories: 
    - Linux
tags:
    - Linux
    - EFI
    - Archlinux  
excerpt: "就最近一次安装Archlinux做一次笔记吧。别问我为什么没事就装arch，因为他是arch  😜"
---
就最近一次安装Archlinux做一次笔记吧。别问我为什么没事就装arch，因为他是arch  😜 

Win10安装就略过了，我的电脑linux老换，win10始终稳稳的在那躺着，一动不动。。。

因为win10是提前安装好的，而他是使用EFI模式安装的 我们在win10下在硬盘上给咋arch挪出点空间来，几十个GB就行。看你硬盘大小了和心情了。

### 启动liveCD
用在[官网](https://www.archlinux.org/download/)或者[163](http://mirrors.163.com/archlinux/iso/2017.09.01/)源下载最新的liveCD镜像（比如最新是archlinux-2017.09.01-x86_64.iso），刻录到u盘，我喜欢用[rufs](http://rufus.akeo.ie/)，这个不大于1M的小工具很简单强大我喜欢。 当然了，熟悉linux的同学可以用[dd](https://wiki.archlinux.org/index.php/Core_utilities#dd)命令。

插上u盘，重启到liveCD里面。里面也是个没桌面的shell系统。

### 看Archlinux wiki
在开始之前最好还是看看[arch的wiki](https://wiki.archlinux.org/index.php/Installation_guide)Installation guide(安装指南)，写的很详细，最好看英文的，中文的翻译有点延迟。

### 联网
如果你是有线网络，插上网线，启动[dhcpd](https://wiki.archlinux.org/index.php/Dhcpcd)服务（也就是动态获取ip）
```bash
systemctl start dhcpcd
```
无线网络用wifi-menu链接
```bash
wifi-menu
```
看看链接成功了没 查看下ip
```
ip addr
```
如果没成功，或者其他的联网方式，看看[arch的wiki](https://wiki.archlinux.org/index.php/Network_configuration)

其实说到这，再次提醒大家，看[arch的wiki](https://wiki.archlinux.org/)

连上网络了，开工吧!

### 分区：
```
fdisk /dev/sda
```
进入fdisk之后按`p`，看看现有分区的情况
这是我的硬盘分区情况，硬盘格式`gpt`
```
/dev/sda1        2048     923647    921600   450M Windows 恢复环境
/dev/sda2      923648    1128447    204800   100M EFI 系统
/dev/sda3     1128448    1161215     32768    16M Microsoft 保留
/dev/sda4     1161216  209256750 208095535  99.2G Microsoft 基本数据
/dev/sda5   209258496  210888703   1630208   796M Windows 恢复环境
/dev/sda6   210890752  462559542 251668791   120G Microsoft 基本数据
```
假如我的硬盘中空闲空间100GB，用fdisk 给这100GB分一分

我的方案是：
```
/dev/sda7    /boot           ext4    500MB    #boot启动分区 500MB够用了
/dev/sda8    swap                        8GB         #交换分区， 我的内存也8GB，我就分一样大小了
/dev/sda9    /                   ext4                   #剩下的全给 / 根分区
```
根据自己的空间大小自行调整。

格式化：
```
mkfs.ext4 /dev/sda7
mkfs.ext4 /dev/sda9
mkswap /dev/sda8
```
### 安装基础系统
挂载分区：
```
mount /dev/sda9 /mnt
mkdir /mnt/boot
mount /dev/sda7 /mnt/boot
mkdir /mnt/boot/EFI
mount /dev/sda2 /mnt/boot/EFI          #这个EFI分区在分区情况里面能看到我的是sda2
swapon /dev/sda8
```
修改源：
```
vim /etc/pacman.d/mirrorlist
```
把163，ustc这几个比较好使的中国源复制到文件最上面，到时候下载安装的时候跑的快一点

开始安装：
```
pacstrap -i /mnt base vim dialog wpa_supplicant     
```
base是基础系统，dialog是上面提到的链接无线网的wifi-menu，wpa_supplicant也是手动链接无线网的包，vim 不解释 

安装完成之后

把分区挂载情况写入fstab中：
```
genfstab -U -p /mnt > /mnt/etc/fstab
```

chroot进新系统：
```
arch-chroot /mnt /bin/bash
```

一些配置：
```
echo 'my-archlinux' > /etc/hostname     #设置主机名字
ln -sf /usr/share/zoneinfo/Asia/Shanghai   #设置时区
timedatectl set-timezone Asia/Shanghai      #也可以用这个设置时区
timedatectl set-ntp true        # 设置时间ntp同步网络时间
vim /etc/locale.gen     # 区域设置 取消英文（en_US.UTF-8）和中文(zh_CN.UTF-8)的注释
locale-gen      #生成设置区域设置
echo "LANG=en_US.UTF-8" > /etc/locale.conf #设置语言，因为先安装的是没桌面的模式，先用英文，中文会显示成小方块
```
 创建初始内存盘[mkinitcpio](https://wiki.archlinux.org/index.php/Mkinitcpio)
```
mkinitcpio -p linux  
```
设置root密码
```
passwd      
```
这步忘了，你就进不去系统了，╮(╯▽╰)╭

搞定启动项：
```
pacman -S grub efibootmgr       #安装grub efibootmgr管理启动项
grub-install --target=x86_64-efi --efi-directory=/boot/EFI --bootloader-id=arch_grub --recheck
pacman -S os-prober     #安装这个包是为了让grub-mkconfig发现win10的启动项
grub-mkconfig -o /boot/grub/grub.cfg   #把启动项写到文件配置里 
```
如果win10的启动项没有被发现，不要着急，在新的系统安装重启之后，重新执行上面`grub-mkconfig`命令就行了

退出新系统,重启
```
exit        #退出
unmout -R /mnt  #取消挂载磁盘
reboot   #重启
```

好了如果一切顺利，重启之后就能看到你的新系统了  (。・∀・)ノ 





