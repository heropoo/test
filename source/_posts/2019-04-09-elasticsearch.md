---
layout: post
title:  "玩转ElasticSearch全文搜索"
date:   2019-04-09 12:19:42
author: "Heropoo"
categories: 
    - 全文搜索
tags:
    - 全文搜索
    - ElasticSearch
    - PHP
    - Docker
excerpt: "玩转ElasticSearch全文搜索"
---
玩转ElasticSearch全文搜索，做个笔记。

## 运行ElasticSearch服务
为了省去繁杂的安装，跳过万恶的环境，我们使用Docker容器来跑ElasticSearch的服务。

我们使用官方的`elasticsearch:5.6.16-alpine`作为基础镜像，另外添加一个中文分词插件`elasticsearch-ik`

我们的Dockerfile内容如下，很简单，只需两行哟：
```
FROM elasticsearch:5.6.16-alpine
RUN elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v5.6.16/elasticsearch-analysis-ik-5.6.16.zip
```

构建镜像：
```
docker build -t heropoo/elasticsearch-ik .
```

运行容器：
```
docker run -d --name elasticsearch -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" heropoo/elasticsearch-ik
```

因为ElasticSearch提供了REST API，我们直接可以用浏览器访问 http://localhost:9200 看看服务是否正常。

## 基本概念
Elastic 本质上是一个分布式数据库，允许多台服务器协同工作，每台服务器可以运行多个 Elastic 实例。单个 Elastic 实例称为一个节点（node）。一组节点构成一个集群（cluster）。

### Index
Elastic 会索引所有字段，经过处理后写入一个反向索引。查找数据的时候，直接查找该索引。所以，Elastic 数据管理的顶层单位就叫做 Index（索引）。

查看当前节点的所有 Index
```
curl -X GET 'http://localhost:9200/_cat/indices?v'
```

### Document
Index 里面单条的记录称为 Document（文档）。许多条 Document 构成了一个 Index。Document 使用 JSON 格式表示。
例如：
```
{
  "user": "张三",
  "title": "工程师",
  "desc": "数据库管理"
}
```
同一个 Index 里面的 Document，不要求有相同的字段结构，但是最好保持相同，这样有利于提高搜索效率。

### Type
不同的 Type 应该有相似的字段结构，举例来说，id字段不能在这个组是字符串，在另一个组是数值。这是与关系型数据库的表的一个区别。性质完全不同的数据（比如products和logs）应该存成两个 Index，而不是一个 Index 里面的两个 Type（虽然可以做到）。

下面的命令可以列出每个 Index 所包含的 Type
```
curl 'localhost:9200/_mapping?pretty=true'
```
pretty参数是优化显示结果易于查看

## Index操作
### 查看当前节点的所有 Index
```
curl -X GET 'http://localhost:9200/_cat/indices?v'
```

### 新建Index
```
curl -X PUT 'http://localhost:9200/weather'
```

### 删除Index
```
curl -X DELETE 'http://localhost:9200/weather'
```

### 新建一个 Index，指定需要分词的字段
```
curl -X PUT 'localhost:9200/accounts' -d '
{
  "mappings": {
    "person": {
      "properties": {
        "user": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        },
        "title": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        },
        "desc": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        }
      }
    }
  }
}'
```
上面代码中，首先新建一个名称为accounts的 Index，里面有一个名称为person的 Type。person有三个字段 user、title、desc 这三个字段都是中文，而且类型都是文本（text），所以需要指定中文分词器，不能使用默认的英文分词器。

## 数据操作
### 新增记录
向指定的 /Index/Type 发送 PUT 请求，就可以在 Index 里面新增一条记录
```
curl -X PUT 'localhost:9200/accounts/person/1' -d '
{
  "user": "张三",
  "title": "工程师",
  "desc": "数据库管理"
}'
```

新增记录的时候，也可以不指定 Id，这时要改成 POST 请求
```
curl -X POST 'localhost:9200/accounts/person' -d '
{
  "user": "李四",
  "title": "工程师",
  "desc": "软件工程师"
}'
```

其实PUT就是存在时修改，不存在时创建。

### 查看记录
向/Index/Type/Id发出 GET 请求，就可以查看这条记录
```
curl 'localhost:9200/accounts/person/1?pretty=true'
```

### 删除记录
```
curl -X DELETE 'localhost:9200/accounts/person/1'
```

### 更新记录
参考上面创建操作，用PUT方法
```
curl -X PUT 'localhost:9200/accounts/person/1' -d '
{
  "user": "张三1",
  "title": "工程师1",
  "desc": "数据库管理1"
}'
```

## 数据查询

### 返回所有记录
```
curl 'localhost:9200/accounts/person/_search'
```

### 全文搜索
```
curl 'localhost:9200/accounts/person/_search'  -d '
{
  "query" : { "match" : { "desc" : "数据库" }}
}' 
```

### 逻辑运算
如果有多个搜索关键字， Elastic 认为它们是or关系
```
curl 'localhost:9200/accounts/person/_search'  -d '
{
  "query" : { "match" : { "desc" : "软件 系统" }}
}'
```

如果要执行多个关键词的and搜索，必须使用布尔查询
```
curl 'localhost:9200/accounts/person/_search'  -d '
{
  "query": {
    "bool": {
      "must": [
        { "match": { "desc": "软件" } },
        { "match": { "desc": "系统" } }
      ]
    }
  }
}'
```

搞定~

参考：http://www.ruanyifeng.com/blog/2017/08/elasticsearch.html










