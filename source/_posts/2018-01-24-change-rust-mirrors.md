---
layout: post
title:  "Rust更换国内源"
date:   2018-01-24 23:25:36
author: "Heropoo"
categories: 
    - Rust
tags:
    - Rust
excerpt: "今天学cargo这节，cargo build 时安装外部依赖真的慢啊。果断找国内源"
---
今天学cargo这节，`cargo build`时安装外部依赖真的慢啊。果断找国内源,果断又是ustc(中科大, emmm以前应该努力一点考中科大~(￣▽￣)~\*)

教程开始：

设置两个环境变量
```sh
export RUSTUP_DIST_SERVER=https://mirrors.ustc.edu.cn/rust-static
export RUSTUP_UPDATE_ROOT=https://mirrors.ustc.edu.cn/rust-static/rustup
```
或者直接写入`~/.bashrc`

使用ustcu的源下载安装rust
```sh
curl -sSf https://mirrors.ustc.edu.cn/rust-static/rustup.sh | sh
```
编辑`~/.cargo/config`写入
```
[registry]
index = "https://mirrors.ustc.edu.cn/crates.io-index/"
[source.crates-io]
registry = "https://github.com/rust-lang/crates.io-index"
replace-with = 'ustc'
[source.ustc]
registry = "https://mirrors.ustc.edu.cn/crates.io-index/"
```
现在cargo安装外部依赖是不是贼溜~😉