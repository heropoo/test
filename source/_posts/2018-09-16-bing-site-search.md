---
layout: post
title:  "使用Bing搜索引擎做站内搜索"
date:   2018-09-15 20:14:05
author: "Heropoo"
categories: 
    - 搜索引擎
tags:
    - 搜索引擎
    - 站内搜索
excerpt: "使用Bing搜索引擎做站内搜索"
---
因为自己的博客百度未收录╮(╯-╰)╭，本来之前用的谷歌，但是谷歌毕竟翻墙才能用。决定还是换微软的Bing搜索引擎来做站内搜索吧。


大概是这样的效果
![example-pic](/assets/images/20180916201818.png)


观察了下，Bing指定站点搜索很简单，比如搜索框输入关键字`git site:www.ioio.pw`提交之后的url是`https://bing.com/search?q=git+site%3Awww.ioio.pw`

所以开始写个简单的form表单就好了，代码如下：
```html
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>bing search</title>
</head>
<body>
	<h1>Bing Search</h1>
	<form action="https://bing.com/search" id="searchForm">
		<input type="text" name="q" value="" required>
		<input type="hidden" name="site" value="www.ioio.pw">
		<input type="submit" value="Search">
	</form>
<script>
	var searchForm = document.getElementById('searchForm');
	searchForm.onsubmit = function(){
		var url = this.action;
		var q = this.children['q'].value;
		var site = this.children['site'].value;
		var url = url + '?q='+q+' site:'+ site;
		window.open(url);
		return false;
	};
</script>
</body>
</html>
```

好了，搞定~
