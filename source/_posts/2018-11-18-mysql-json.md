---
layout: post
title:  "MySQL5.7的JSON基本操作"
date:   2018-11-18 18:11:06
author: "heropoo"
categories: 
    - MySQL
tags: 
    - MySQL
    - JSON

excerpt: "MySQL从5.7版本开始就支持JSON格式的数据，操作用起来挺方便的"
---
MySQL从5.7版本开始就支持JSON格式的数据，操作用起来挺方便的。

### 建表
在新建表时字段类型可以直接设置为json类型，比如我们创建一张表：
```
mysql> CREATE TABLE `test_user`(`id` INT PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(50) NOT NULL, `info` JSON);
```
json类型字段可以为NULL

### 插入数据：
```
mysql> INSERT INTO test_user(`name`, `info`) VALUES('xiaoming','{"sex": 1, "age": 18, "nick_name": "小萌"}');
```
json类型的字段必须时一个有效的json字符串


可以使用JSON_OBJECT()函数构造json对象：
```
mysql> INSERT INTO test_user(`name`, `info`) VALUES('xiaohua', JSON_OBJECT("sex", 0, "age", 17));
```

使用JSON_ARRAY()函数构造json数组：
```
mysql> INSERT INTO test_user(`name`, `info`) VALUES('xiaozhang', JSON_OBJECT("sex", 1, "age", 19, "tag", JSON_ARRAY(3,5,90)));
```

现在查看`test_user`表中的数据：
```
mysql> select * from test_user;
+----+-----------+--------------------------------------------+
| id | name      | info                                       |
+----+-----------+--------------------------------------------+
|  1 | xiaoming  | {"age": 18, "sex": 1, "nick_name": "小萌"} |
|  2 | xiaohua   | {"age": 17, "sex": 0}                      |
|  3 | xiaozhang | {"age": 19, "sex": 1, "tag": [3, 5, 90]}   |
+----+-----------+--------------------------------------------+
3 rows in set (0.04 sec)
```

### 查询
表达式： 对象为`json列->'$.键'`, 数组为`json列->'$.键[index]'`
```
mysql> select name, info->'$.nick_name', info->'$.sex', info->'$.tag[0]' from test_user;
+-----------+---------------------+---------------+------------------+
| name      | info->'$.nick_name' | info->'$.sex' | info->'$.tag[0]' |
+-----------+---------------------+---------------+------------------+
| xiaoming  | "小萌"              | 1             | NULL             |
| xiaohua   | NULL                | 0             | NULL             |
| xiaozhang | NULL                | 1             | 3                |
+-----------+---------------------+---------------+------------------+
3 rows in set (0.04 sec)
```

等价于：对象为`JSON_EXTRACT(json列 , '$.键')`，数组为`JSON_EXTRACT(json列 , '$.键[index]')`
```
mysql> select name, JSON_EXTRACT(info, '$.nick_name'), JSON_EXTRACT(info, '$.sex'), JSON_EXTRACT(info, '$.tag[0]')  from test_user;
+-----------+-----------------------------------+-----------------------------+--------------------------------+
| name      | JSON_EXTRACT(info, '$.nick_name') | JSON_EXTRACT(info, '$.sex') | JSON_EXTRACT(info, '$.tag[0]') |
+-----------+-----------------------------------+-----------------------------+--------------------------------+
| xiaoming  | "小萌"                            | 1                           | NULL                           |
| xiaohua   | NULL                              | 0                           | NULL                           |
| xiaozhang | NULL                              | 1                           | 3                              |
+-----------+-----------------------------------+-----------------------------+--------------------------------+
3 rows in set (0.04 sec)
```

不过看到上面`"小萌"`是带双引号的，这不是我们想要的，可以用`JSON_UNQUOTE`函数将双引号去掉
```
mysql> select name, JSON_UNQUOTE(info->'$.nick_name') from test_user where name='xiaoming';
+----------+-----------------------------------+
| name     | JSON_UNQUOTE(info->'$.nick_name') |
+----------+-----------------------------------+
| xiaoming | 小萌                              |
+----------+-----------------------------------+
1 row in set (0.05 sec)
```

也可以直接使用操作符`->>`
```
mysql> select name, info->>'$.nick_name' from test_user where name='xiaoming';
+----------+----------------------+
| name     | info->>'$.nick_name' |
+----------+----------------------+
| xiaoming | 小萌                 |
+----------+----------------------+
1 row in set (0.06 sec)
```

当然属性也可以作为查询条件
```
mysql> select name, info->>'$.nick_name' from test_user where info->'$.nick_name'='小萌';
+----------+----------------------+
| name     | info->>'$.nick_name' |
+----------+----------------------+
| xiaoming | 小萌                 |
+----------+----------------------+
1 row in set (0.05 sec)
```

值得一提的是，可以通过虚拟列对JSON类型的指定属性进行快速查询。

创建虚拟列:
```
mysql> ALTER TABLE `test_user` ADD `nick_name` VARCHAR(50) GENERATED ALWAYS AS (info->>'$.nick_name') VIRTUAL;
```
注意用操作符`->>`

使用时和普通类型的列查询是一样:
```
mysql> select name,nick_name from test_user where nick_name='小萌';
+----------+-----------+
| name     | nick_name |
+----------+-----------+
| xiaoming | 小萌      |
+----------+-----------+
1 row in set (0.05 sec)
```

### 更新
使用`JSON_INSERT()`插入新值，但不会覆盖已经存在的值
```
mysql> UPDATE test_user SET info = JSON_INSERT(info, '$.sex', 1, '$.nick_name', '小花') where id=2;
```
看下结果
```
mysql> select * from test_user where id=2;
+----+---------+--------------------------------------------+-----------+
| id | name    | info                                       | nick_name |
+----+---------+--------------------------------------------+-----------+
|  2 | xiaohua | {"age": 17, "sex": 0, "nick_name": "小花"} | 小花      |
+----+---------+--------------------------------------------+-----------+
1 row in set (0.06 sec)
```

使用`JSON_SET()`插入新值，并覆盖已经存在的值
```
mysql> UPDATE test_user SET info = JSON_SET(info, '$.sex', 0, '$.nick_name', '小张') where id=3;
```
看下结果
```
mysql> select * from test_user where id=3;
+----+-----------+---------------------------------------------------------------+-----------+
| id | name      | info                                                          | nick_name |
+----+-----------+---------------------------------------------------------------+-----------+
|  3 | xiaozhang | {"age": 19, "sex": 1, "tag": [3, 5, 90], "nick_name": "小张"} | 小张      |
+----+-----------+---------------------------------------------------------------+-----------+
1 row in set (0.06 sec)
```

使用`JSON_REPLACE()`只替换存在的值
```
mysql> UPDATE test_user SET info = JSON_REPLACE(info, '$.sex', 1, '$.tag', '[1,2,3]') where id=2;
```
看下结果
```
mysql> select * from test_user where id=2;
+----+---------+--------------------------------------------+-----------+
| id | name    | info                                       | nick_name |
+----+---------+--------------------------------------------+-----------+
|  2 | xiaohua | {"age": 17, "sex": 1, "nick_name": "小花"} | 小花      |
+----+---------+--------------------------------------------+-----------+
1 row in set (0.06 sec)
```
可以看到tag没有更新进去

### 删除
使用`JSON_REMOVE()`删除JSON元素
```
mysql> UPDATE test_user SET info = JSON_REMOVE(info, '$.sex', '$.tag') where id=1;
```
看下结果
```
mysql> select * from test_user where id=1;
+----+----------+----------------------------------+-----------+
| id | name     | info                             | nick_name |
+----+----------+----------------------------------+-----------+
|  1 | xiaoming | {"age": 18, "nick_name": "小萌"} | 小萌      |
+----+----------+----------------------------------+-----------+
1 row in set (0.05 sec)
```

最后从MySQL的官方网站查看帮助文档：http://dev.mysql.com/doc/refman/5.7/en/json.html

😎