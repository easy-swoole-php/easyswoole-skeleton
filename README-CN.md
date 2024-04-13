[English](./README.md) | 中文

![](easyswoole.png)

[![Latest Stable Version](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/v/stable)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Total Downloads](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/downloads)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Latest Unstable Version](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/v/unstable)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![License](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/license)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Monthly Downloads](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/d/monthly)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)

# 骨架介绍

这是一个使用 `EasySwoole` 框架搭建的骨架应用程序。这个骨架让开发者更容易使用 `EasySwoole` 框架。该应用程序旨在作为那些希望熟悉 `EasySwoole` 框架的人的起点。

# 安装要求

`EasySwoole` 对系统环境有一些要求，只能在 `Linux` 和 `Mac` 环境下运行，但由于 `Docker` 虚拟化技术的发展，在 `Windows` 下 `Docker for Windows` 也可以作为运行环境。

各个版本的 `Dockerfile` 在 [XueSiLf/easyswoole-docker](https://github.com/XueSiLf/easyswoole-docker) 项目中已经给你准备好了，或者直接基于 `EasySwoole` 官方已经构建的 [easyswoolexuesi2021/easyswoole](https://hub.docker.com/repository/docker/easyswoolexuesi2021/easyswoole) 镜像来运行。

当你不想使用 `Docker` 作为运行环境时，你需要确保你的运行环境满足以下要求：

- PHP >= 7.4
- Swoole PHP 扩展 >= 4.4.23 且 Swoole PHP 扩展 <= 4.4.26
- JSON PHP 扩展
- Pcntl PHP 扩展
- OpenSSL PHP 扩展（如果需要使用 `HTTPS`）

# 使用 Composer 安装

创建新 `EasySwoole` 项目的最简单方法是使用 [Composer](https://getcomposer.org/)。 如果您尚未安装，请按照[文档](https://getcomposer.org/download/)安装。

创建新的 `EasySwoole` 项目：

## 安装 3.5.x 版本

```bash
composer create-project easy-swoole-php/easyswoole-skeleton="3.5.1" project_name
```

如果你的开发环境是基于 `Docker` 的，你可以使用官方的 `Composer` 镜像来创建一个新的 `EasySwoole` 项目：

```bash
docker run --rm -it -v $(pwd):/app composer create-project --ignore-platform-reqs easy-swoole-php/easyswoole-skeleton="3.5.1" project_name
```

## 安装 3.7.x 版本

```bash
composer create-project easy-swoole-php/easyswoole-skeleton="3.7.1" project_name
```

如果你的开发环境是基于 `Docker` 的，你可以使用官方的 `Composer` 镜像来创建一个新的 `EasySwoole` 项目：

```bash
docker run --rm -it -v $(pwd):/app composer create-project --ignore-platform-reqs easy-swoole-php/easyswoole-skeleton="3.7.1" project_name
```

# 入门

安装后，您可以使用以下命令立即运行服务器。

```bash
cd project_name
php easyswoole server start # for the Development environment
# php easyswoole server start -mode=dev # for the Development environment
# php easyswoole server start -mode=dev -d # for the Development environment with daemonize
# php easyswoole server start -mode=test # for the Test environment
# php easyswoole server start -mode=uat # for the User Acceptance Testing environment
# php easyswoole server start -mode=produce # for the Production environment
```

或者，如果在基于 `Docker` 的环境中，您可以使用骨架提供的 `docker-compose/docker-compose.yml`：

```bash
# install packages
cd project_name
docker run --rm -it -v $(pwd):/app composer install --ignore-platform-reqs

# start services
cd project_name/docker-compose
docker-compose up
```

这将在端口 `9501` 上启动 `cli-server`，并将其绑定到所有网络接口。然后您可以访问该网站在 `http://localhost:9501/` ，这将打开 `EasySwoole` 默认主页。

# 建议

- 建议您将骨架中部分文件中的项目名称重命名为您实际的项目名称，例如像 `composer.json` 和 `docker-compose.yml` 这样的文件。
- 查看 `App/HttpController/Index.php` 以查看 HTTP 入口点的示例。

**请记住**：您始终可以将此 `README.md` 文件的内容替换为适合您项目的内容描述。

## 联系我们

问题：[https://github.com/easy-swoole/easyswoole/issues](https://github.com/easy-swoole/easyswoole/issues)

加群请加微信：

<img src="https://raw.githubusercontent.com/easy-swoole-php/easyswoole-skeleton/main/contactus.jpg" width="210">
