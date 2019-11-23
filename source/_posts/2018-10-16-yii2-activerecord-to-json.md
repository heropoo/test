---
layout: post
title:  "关于Yii2查询数据对象ActiveRecord的json序列化问题"
date:   2018-10-16 17:53:45
author: "heropoo"
categories: 
    - PHP
tags: 
    - PHP
    - Yii2

excerpt: "关于Yii2查询数据对象ActiveRecord的json序列化问题"
---
今天在使用`json_encode`函数序列化Yii2查询得到的数据模型对象（通常继承`ActiveRecord`类）时，发现返回结果是`{}`,而不是预期的数据库字段键值对这种形式：`{"name"： "xxx", "sex": 1}`

后来赶时间就直接在手动处理了下，改动了下模型对象让它接入`JsonSerializable`接口，并实现其接口方法，类似这样；
```php
//...

class User extends ActiveRecord implements \JsonSerializable{

	//... 其他代码

	public function jsonSerialize (){
		return $this->toArray();
	}
}
```
然后完美的解决了。 

但是后来代码写完了，觉得这个框架应该提供了这个问题的解决方法了吧。然后找了找，果然有的： `\yii\helpers\BaseJson::encode($user)`。然后翻了翻源代码，部分代码是这样的：
```php
//from \yii\helpers\BaseJson::processData
if (is_object($data)) {
    if ($data instanceof JsExpression) {
        $token = "!{[$expPrefix=" . count($expressions) . ']}!';
        $expressions['"' . $token . '"'] = $data->expression;

        return $token;
    } elseif ($data instanceof \JsonSerializable) {
        $data = $data->jsonSerialize();
    } elseif ($data instanceof Arrayable) { // <---here
        $data = $data->toArray();
    } elseif ($data instanceof \SimpleXMLElement) {
        $data = (array) $data;
    } else {
        $result = [];
        foreach ($data as $name => $value) {
            $result[$name] = $value;
        }
        $data = $result;
    }

    if ($data === []) {
        return new \stdClass();
    }
}
```
作者在处理数据的时候做了判断，`ActiveRecord`类接了`Arrayable`接口，然后作者也是使用`toArray()`方法。然后我觉得放心了。我的改造也没有错。当然了使用框架提供的方法更简单点。
