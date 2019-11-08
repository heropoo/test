---
layout: post
title:  "SSH使用密码自动登录脚本"
date:   2019-08-28 17:19:56
author: "Heropoo"
categories: 
    - Linux
tags:
    - Linux
excerpt: "SSH使用密码自动登录脚本"
---
分享一个ssh使用密码登录的脚本

写个脚本`autologin.sh`内容如下：
```sh
#!/usr/bin/expect -f
set user [lindex $argv 0]
set host [lindex $argv 1]
set password [lindex $argv 2]
set timeout -1

spawn ssh $user@$host
expect "password:*"
send "$password\r"
interact
expect eof
```

并给这个脚本可执行权限
```sh
chmod +x ./autologin.sh
```

* 使用方法：
假如平时使用`ssh root@127.0.0.1`,然后输入密码`123456`登录

现在用这个脚本就是
```sh
./autologin.sh root 127.0.0.1 123456
```

挺方便的吧～