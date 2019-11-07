---
layout: post
title:  "关于PHP Composer 版本号前置~与^符号的区别(转)"
date:   2018-12-29 15:14:57
author: "heropoo"
categories: 
    - PHP
tags: 
    - PHP
    - Composer

excerpt: "关于PHP Composer 版本号前置~与^符号的区别"
---
`~`和`^`的意思很接近，在`x.y`的情况下是一样的，都是代表`x.y <= 版本号 < (x+1).0`。但是在版本号是`x.y.z`的情况下有区别，举个例子：

* `~1.2.3` 代表 `1.2.3 <= 版本号 < 1.3.0`

* `^1.2.3` 代表 `1.2.3 <= 版本号 < 2.0.0`

原文链接： https://www.cnblogs.com/hcpzhe/p/7909651.html
