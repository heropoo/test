---
layout: post
title:  "使用PHP写Git的自动部署webhook脚本"
date:   2018-05-01 10:16:43
author: "Heropoo"
categories: 
    - Git
    - PHP
tags:
    - Git
    - PHP
    - Webhook
excerpt: "现在开发项目大多使用git作为版本控制器，而且现在大多数的代码托管平台都支持自定义webhook脚本"
---
现在开发项目大多使用git作为版本控制器，而且现在大多数的代码托管平台都支持自定义webhook脚本。正好利用这个脚本，结合git的workflow，我们可以轻松的做到项目代码的自动发布部署。

### 最简单的流程
比如你的项目有两个分支，一个是代码已经经过测试可用于部署到服务器的master分支，一个用于开发的dev分支。那么我们上线的过程就是merge dev的代码到master分支。那么我们可以设置一个代码push触发的webhook。这个webhook脚本的代码也非常简单，就是`git pull origin master`。

### 最简单的实现
* 编写`webhook.php`

```php
<?php

// todo 在此可以写校验权限密码之类的代码


$path = dirname(__DIR__);
$log_file = $path.'/runtime/logs/webhook-pull-error-output.log';	//错误日志文件的路径

$descriptorspec = array(
    0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
    1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
    2 => array("file", $log_file, "a") // 标准错误，写入到一个文件
);

$cwd = $path;
$env = array('PATH' => $_SERVER['PATH']);

$process = proc_open('sudo git pull origin master', $descriptorspec, $pipes, $cwd, $env);

echo '<pre>';
if (is_resource($process)) {
    // $pipes 现在看起来是这样的：
    // 0 => 可以向子进程标准输入写入的句柄
    // 1 => 可以从子进程标准输出读取的句柄
    // 错误输出将被追加到文件 /tmp/error-output.txt

    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
    $return_value = proc_close($process);

    echo PHP_EOL."command returned $return_value\n";
}
```

* 添加php脚本的执行用户sudoers中

比如你使用nginx+php-fpm的服务器架构，你的php-fpm的用户是www-data
```sh
visudo
--------------------------------
...
#Defaults   !visiblepw   #注释掉这句 这句是限制sudo只能在命令行执行的
www-data ALL=(ALL:ALL) NOPASSWD: /usr/bin/git
```

* push代码到master分支测试下吧~ 登陆服务器看看代码是不是已经同步好了　
