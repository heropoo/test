---
layout: post
title:  "关于大文件上传"
date:   2019-05-23 10:06:28
author: "Heropoo"
categories: 
    - JavaScript
    - PHP
tags:
    - JavaScript
    - HTML5
    - AJAX 
    - PHP
excerpt: "最近做视频上传，我们使用切片上传大文件"
---
最近在做视频上传，我们使用切片上传大文件，做个笔记。

## 思路
* 使用js读取form表单中选择的file，计算文件md5值，并上传md5值到服务端，检查文件是否已上传过（类似秒传功能）
* 若文件未上传过，按照其大小切成1MB大小的块，小于1MB的不用切
* 用ajax异步提交切好的块上传至服务端（一个块一个请求，不阻塞，多线程）
* 当上传完成所有切块，发起一个合并文件的请求，服务端进行前面上传的文件块的合并，合并完成即上传完成。

## 实现
js计算文件md5使用[spark-md5.js](https://www.npmjs.com/package/spark-md5),据说这个库使用的是世界上最快的md5算法。

js对文件切片并使用ajax上传切片
```javascript
//...
let size = file.size; //获取文件大小
const shardSize = 1024 * 1024; // 块大小1MB
let shardCount = Math.ceil(size/shardSize); //可切成的块数

for(let i = 0; i < shardCount; i++){
  let start = i * shardSize,
      end = Math.min(size, start + shardSize);
  let form = new FormData();
  form.append('file', file.slice(start, end));  //用slice方法切片
  form.append('size', end - start);
  form.append('name', name);
  form.append('total', shardCount);
  form.append('md5', file_md5); //文件md5值
  form.append('index', i);  //第几块

  $.ajax({
    url: 'upload.php?type=shard',
    type: "POST",
    data: form,
    // async: false,     //是否异步上传，默认true
    processData: false, //很重要，告诉jquery不要对form进行处理
    contentType: false, //很重要，指定为false才能形成正确的Content-Type
    success: function (res) {
      // 成功回调
    }
  }
}
```

php端保存切片
```php
$path = __DIR__ . '/uploads';
$file = $_FILES['file'];
$total = $_POST['total'];
$index = $_POST['index'];
$size = $_POST['size'];
$dst_file = $path . '/' . $name . '-' . $total . ':' . $index;  // 切片文件存储的文件名 
if ($file["error"] > 0) {
    echo json_encode(['code'=>400, 'msg'=>$file["error"]]);die;
} else {
    $res = move_uploaded_file($file['tmp_name'], $dst_file);
    if ($res) {
        file_put_contents($dst_file . '.info', $size);  // 切片上传成功，写一个保存其大小的文件，后续合并是校验文件用的到
        echo json_encode(['code'=>200, 'msg'=>'shard ok']);die;
    } else {
        echo json_encode(['code'=>400, 'msg'=>'shard move_uploaded_file error']);die;
    }
}
```

php端合并
```php
//...
function mergeFile($name, $total, &$msg)
{
    // 校验切片文件是否都上传完成，是否完整
    for ($i = 0; $i < $total; $i++) { 
        if (!file_exists($name . '-' . $total . ':' . $i . '.info') || !file_exists($name . '-' . $total . ':' . $i)) {
            $msg = "shard error $i";
            return false;
        } else if (filesize($name . '-' . $total . ':' . $i) != file_get_contents($name . '-' . $total . ':' . $i . '.info')) {
            $msg = "shard size error $i";
            return false;
        }
    }
    @unlink($name);
    if (file_exists($name . '.lock')) {   //加锁 防止有其他进程写文件，造成文件损坏
        $msg = 'on lock';
        return false;
    }
    touch($name . '.lock');
    $file = fopen($name, 'a+');
    for ($i = 0; $i < $total; $i++) {   //按切片顺序写入文件
        $shardFile = fopen($name . '-' . $total . ':' . $i, 'r');
        $shardData = fread($shardFile, filesize($name . '-' . $total . ':' . $i));
        fwrite($file, $shardData);
        fclose($shardFile);
        unlink($name . '-' . $total . ':' . $i); 
        unlink($name . '-' . $total . ':' . $i . '.info');
    }
    fclose($file);
    unlink($name . '.lock');
    return true;
}
```

我也写好了一个demo，[传送门](https://github.com/heropoo/just-code/tree/master/upload-large-file)

下面是这个demo的效果图：

![pic-0](/assets/images/WX20190523-103939.png)
![pic-1](/assets/images/WX20190523-104043.png)

这个demo有些方面还不够完善，后续持续完善吧～