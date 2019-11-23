---
layout: post
title:  "MySQL权限管理"
date:   2019-06-10 12:47:00
author: "Heropoo"
categories: 
    - MySQL 
tags:
    - MySQL
excerpt: "MySQL中的账号与权限管理"
---
MySQL中的账号与权限管理

### 权限系统的工作原理
MySQL权限系统通过下面两个阶段进行认证：
1. 对连接的用户进行身份认证，合法的用户通过认证、不合法的用户拒绝连接。
2. 对通过认证的合法用户赋予相应的权限，用户可以在这些权限范围内对数据库做相应的操作。

对于身份，MySQL是通过`IP地址`和`用户名`联合进行确认的，例如MySQL安装默认创建的用户`root@localhost`表示用户root只能从本地（localhost）进行连接才可以通过认证，此用户从其他任何主机对数据库进行的连接都将被拒绝。也就是说，同样的一个用户名，如果来自不同的IP地址，则MySQL将其视为不同的用户。

MySQL的权限表在数据库启动地时候就载入内存，当用户通过身份认证后，就在内存中进行相应权限的存取，这样，此用户就可以在数据库中做权限范围内的各种操作了。

### 权限表
系统会用到名叫“mysql”数据库（安装MySQL时被创建）中user表作为权限表

我们看看user表的结构（注：本文示例使用的是MySQL5.7.25版本）
```
mysql> desc user;
+------------------------+-----------------------------------+------+-----+-----------------------+-------+
| Field                  | Type                              | Null | Key | Default               | Extra |
+------------------------+-----------------------------------+------+-----+-----------------------+-------+
| Host                   | char(60)                          | NO   | PRI |                       |       |
| User                   | char(32)                          | NO   | PRI |                       |       |
| Select_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Insert_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Update_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Delete_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Create_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Drop_priv              | enum('N','Y')                     | NO   |     | N                     |       |
| Reload_priv            | enum('N','Y')                     | NO   |     | N                     |       |
| Shutdown_priv          | enum('N','Y')                     | NO   |     | N                     |       |
| Process_priv           | enum('N','Y')                     | NO   |     | N                     |       |
| File_priv              | enum('N','Y')                     | NO   |     | N                     |       |
| Grant_priv             | enum('N','Y')                     | NO   |     | N                     |       |
| References_priv        | enum('N','Y')                     | NO   |     | N                     |       |
| Index_priv             | enum('N','Y')                     | NO   |     | N                     |       |
| Alter_priv             | enum('N','Y')                     | NO   |     | N                     |       |
| Show_db_priv           | enum('N','Y')                     | NO   |     | N                     |       |
| Super_priv             | enum('N','Y')                     | NO   |     | N                     |       |
| Create_tmp_table_priv  | enum('N','Y')                     | NO   |     | N                     |       |
| Lock_tables_priv       | enum('N','Y')                     | NO   |     | N                     |       |
| Execute_priv           | enum('N','Y')                     | NO   |     | N                     |       |
| Repl_slave_priv        | enum('N','Y')                     | NO   |     | N                     |       |
| Repl_client_priv       | enum('N','Y')                     | NO   |     | N                     |       |
| Create_view_priv       | enum('N','Y')                     | NO   |     | N                     |       |
| Show_view_priv         | enum('N','Y')                     | NO   |     | N                     |       |
| Create_routine_priv    | enum('N','Y')                     | NO   |     | N                     |       |
| Alter_routine_priv     | enum('N','Y')                     | NO   |     | N                     |       |
| Create_user_priv       | enum('N','Y')                     | NO   |     | N                     |       |
| Event_priv             | enum('N','Y')                     | NO   |     | N                     |       |
| Trigger_priv           | enum('N','Y')                     | NO   |     | N                     |       |
| Create_tablespace_priv | enum('N','Y')                     | NO   |     | N                     |       |
| ssl_type               | enum('','ANY','X509','SPECIFIED') | NO   |     |                       |       |
| ssl_cipher             | blob                              | NO   |     | NULL                  |       |
| x509_issuer            | blob                              | NO   |     | NULL                  |       |
| x509_subject           | blob                              | NO   |     | NULL                  |       |
| max_questions          | int(11) unsigned                  | NO   |     | 0                     |       |
| max_updates            | int(11) unsigned                  | NO   |     | 0                     |       |
| max_connections        | int(11) unsigned                  | NO   |     | 0                     |       |
| max_user_connections   | int(11) unsigned                  | NO   |     | 0                     |       |
| plugin                 | char(64)                          | NO   |     | mysql_native_password |       |
| authentication_string  | text                              | YES  |     | NULL                  |       |
| password_expired       | enum('N','Y')                     | NO   |     | N                     |       |
| password_last_changed  | timestamp                         | YES  |     | NULL                  |       |
| password_lifetime      | smallint(5) unsigned              | YES  |     | NULL                  |       |
| account_locked         | enum('N','Y')                     | NO   |     | N                     |       |
+------------------------+-----------------------------------+------+-----+-----------------------+-------+
45 rows in set (0.00 sec)
```
当用户进行连接的时候，权限表的存取过程有以下现个阶段。
* 先从user表中的`Host`、`User`、`authentication_string`(密码)、`password_expired`和`password_lifetime`这几个字段中判断连接的IP、用户名和密码是否存在于表中，如果存在，则通过身份验证，否则拒绝连接。
* 如果验证通过，再通过以`_priv`结尾的那些枚举字段（这些都是用户的权限开关（Y/N））得到用户拥有的权限。

### 账号管理
账号管理主要包括账号的创建、权限更改和账号的删除。用户连接数据库的第一步都从账号创建开始。

有两种方法可以用来创建账号：使用`GRANT`语法创建或者直接操作授权表，但更推荐使用第一种方法，因为操作简单，出错几率更少。

我们用几个例子来说明吧：

1. 创建用户

    创建用户tom，权限为可以在所有数据库上执行所有权限，只能从本地进行连接。
    ```
    mysql> GRANT ALL PRIVILEGES ON *.* TO tom@localhost IDENTIFIED BY 'tompassword' WITH GRANT OPTION;
    ```
    如果你执行这个语句碰到以下错误：`ERROR 1819 (HY000): Your password does not satisfy the current policy requirements`。这个是密码策略的问题，请设置比较复杂的密码，或者修改密码策略，这里就不详细说了。
    
    GRANT命令说明：
    > `ALL PRIVILEGES`是表示所有权限，你也可以使用select、update等权限。
    
    > `ON`用来指定权限针对哪些库和表，格式是`数据库名.表名`，这里`*.*`表示所有数据库和所有表。
    
    > `TO` 表示将权限赋予某个用户。`tom@localhost`，表示`tom`用户，`@`后面接限制的主机，可以是`IP`、`IP段`、`域名`以及`%`，`%`表示任何地方。注意：这里%有的版本不包括本地，以前碰到过给某个用户设置了%允许任何地方登录，但是在本地登录不了，这个和版本有关系，遇到这个问题再加一个localhost的用户就可以了。
    
    > `IDENTIFIED BY` 指定用户的登录密码， 这里`'tompassword'`就是用户tom的密码。
    
    > `WITH GRANT OPTION` 这个选项表示该用户可以将自己拥有的权限授权给别人。注意：经常有人在创建操作用户的时候不指定WITH GRANT OPTION选项导致后来该用户不能使用GRANT命令创建用户或者给其它用户授权。
    
    备注：可以使用`GRANT`重复给用户添加权限，权限叠加，比如你先给用户添加一个select权限，然后又给用户添加一个insert权限，那么该用户就同时拥有了select和insert权限。
    
    使用`GRANT`操作用户权限之后，再使用`FLUSH PRIVILEGES`命令来刷新权限使其立即生效
    ```
    mysql> FLUSH PRIVILEGES;
    Query OK, 0 rows affected (0.00 sec)
    ```

2. 查看用户的权限

    直接使用`SHOW GRANTS`默认查看`root@localhost`的权限
    ```
    mysql> SHOW GRANTS;
    +---------------------------------------------------------------------+
    | Grants for root@localhost                                           |
    +---------------------------------------------------------------------+
    | GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION |
    | GRANT PROXY ON ''@'' TO 'root'@'localhost' WITH GRANT OPTION        |
    +---------------------------------------------------------------------+
    2 rows in set (0.01 sec)
    ```
    
    查看某个用户的权限
    ```
    mysql> SHOW GRANTS FOR tom@localhost;
    +----------------------------------------------------------------------+
    | Grants for tom@localhost                                           |
    +----------------------------------------------------------------------+
    | GRANT ALL PRIVILEGES ON *.* TO 'tom'@'localhost' WITH GRANT OPTION |
    +----------------------------------------------------------------------+
    1 row in set (0.00 sec)
    ```

3. 收回权限
    ```
    mysql> REVOKE DELETE ON *.* FROM 'tom'@'localhost';
    Query OK, 0 rows affected (0.00 sec)
    ```

4. 对用户账户重命名
    ```
    mysql> RENAME USER tom@localhost to jerry@localhost;
    Query OK, 0 rows affected (0.00 sec)
    ```

5. 删除用户
    ```
    mysql> DROP USER jerry@localhost;
    Query OK, 0 rows affected (0.01 sec)
    ```

6. 修改和重置密码

    * 用`SET PASSWORD`命令修改密码
    ```
    mysql> SET PASSWORD FOR root@localhost = PASSWORD('123456');
    Query OK, 0 rows affected, 1 warning (0.01 sec)
    ```

    * 直接修改user表
    ```
    mysql> UPDATE user SET authentication_string=PASSWORD('123456root') WHERE user='root' and host='localhost';
    Query OK, 1 row affected, 1 warning (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 1
    
    mysql> FLUSH PRIVILEGES;
    Query OK, 0 rows affected (0.00 sec)
    ```

    * 在未登录mysql的情况下用mysqladmin命令修改密码
    ```
    $ mysqladmin -uroot -p123456root password 123321
    ```

    * 在丢失root密码的时候
    关闭mysql服务（根据你自己的操作系统自行关闭），然后跳过权限认证启动mysql服务
    ```
    $ mysqld_safe --skip-grant-tables &   
    ```
 
    无密码登陆
    ```
    $ mysql -uroot
    ```
    进入之后使用上面直接修改user表的方法修改root用户的密码
    
    最后杀掉`mysqld_safe`和`mysqld`的进程
    
    重新启动mysql服务，用新的密码登陆吧。








