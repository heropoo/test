---
layout: post
title:  "从SSH私钥中重新生成公钥"
date:   2018-11-19 23:59:52
author: "heropoo"
categories: 
    - SSH
tags: 
    - SSH

excerpt: "从SSH私钥中重新生成公钥"
---
从SSH私钥中重新生成公钥

假如我们的私钥是`id_rsa`
```sh
ssh-keygen -y -f id_rsa
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDdqNYfRhP/4Y1Kwr5/ZfRPbQkDEKQ9sLpGYex2bzXsPIXZmpgI4yUkLkQRCyvrMoZQKOcabb+GgnrYJvPR1rO/CVI9bfUw+MD1OFvnJUI2deATTeMj2hlY/IDSS1q3AG1ZEztFLizTiJqZvkjx/WPXR/b7ZIVV5DRIeaUMCfEjNIRD+spcJ5ALBuwGPO+4irAXIxgTxbYMDD2ASnpr6v7oSzc0N5ZhZ7rV1dk6hA/RowqYO7DwIvZtOAc55sv6pSUYG3RUJhnkzcmE5VTbyTMKB6O738np6DEw5soWdL1ITPgLE+uJcTt8tcOmPkBXv+45A4TuJ5ksONil8xhfVYG3
```

可以直接重定向到文件
```sh
ssh-keygen -y -f id_rsa > id_rsa.pub
```