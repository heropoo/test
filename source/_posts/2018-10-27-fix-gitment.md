---
layout: post
title:  "修复gitment评论"
date:   2018-10-27 16:17:34
author: "heropoo"
categories: 
    - github-issue
tags: 
    - github-issue
    - gitment

excerpt: "最近几个月博客外挂的gitment评论老是挂，也一直没时间修复。这几天想办法修好了。就说说修复的过程吧"
---
最近几个月博客外挂的gitment评论老是挂，也一直没时间修复。这几天想办法修好了。就说说修复的过程吧。

从我开始用gitment的评论系统，总共出现过两次问题：

## 1. github-issue label的字符长度限制
这次导致原本默认使用网页url地址做label初始化评论的方式无法使用，url太长了超过了50个字符。而之前已经初始化好的评论（issue）不收影响。所以后来我想了个办法来改，就是用时间来初始化，并且对于为了兼容之前的评论，加了时间判断：
```js
var page_date = '{{ page.date }}';
var id = window.location.href;
if(page_date > '2018-04-31 00:00:00 +0000'){
    id = page_date;
}
var gitment = new Gitment({
    id: id, // 可选。默认为 location.href
    owner: 'heropoo',
    repo: 'heropoo.github.io',
    oauth: {
        client_id: 'cccc',
        client_secret: 'xxxx',
    },
});
gitment.render('container');
```

## 2. gitment 作者提供的oauth授权服务不可用
这次好像挂了使用作者提供的js的博客全挂了😂。 解决办法就是自己搭建或者使用别人搭建的oauth授权服务了。 好吧，换个授权服务总算好了。

## 3. 升级https
现在github-page提供强制https。升级之后发现之前兼容的使用页面url初始化的评论又不能用了。 ㄟ( ▔, ▔ )ㄏ
但是还得修嘛。后来仔细观察了下，原来作者是使用`gitment`和页面js中获取的id来匹配出一个issue的。所以只要去github修改issue label就可以了。把原来很长的url label改成现在的短的，比如像我一样用时间。然后页面上的兼容的js也去掉就搞定了。
```js
var page_date = '{{ page.date }}';
//var id = window.location.href;
//if(page_date > '2018-04-31 00:00:00 +0000'){
var id = page_date;
//}
var gitment = new Gitment({
    id: id, // 可选。默认为 location.href
    owner: 'heropoo',
    repo: 'heropoo.github.io',
    oauth: {
        client_id: 'cccc',
        client_secret: 'xxxx',
    },
});
gitment.render('container');
```

好了，搞完收工。