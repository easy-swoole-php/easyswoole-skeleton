English | [中文](./README-CN.md)

![](easyswoole.png)

[![Latest Stable Version](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/v/stable)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Total Downloads](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/downloads)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Latest Unstable Version](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/v/unstable)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![License](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/license)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)
[![Monthly Downloads](https://poser.pugx.org/easy-swoole-php/easyswoole-skeleton/d/monthly)](https://packagist.org/packages/easy-swoole-php/easyswoole-skeleton)

# Introduction

This is a skeleton application using the `EasySwoole` framework. This skeleton that makes it easier for developers to use the `EasySwoole` framework. This application is meant to be used as a starting place for those looking to get their feet wet with `EasySwoole` Framework.

# Requirement

`EasySwoole` has some requirements for the system environment, it can only run under `Linux` and `Mac` environment, but due to the development of `Docker` virtualization technology, `Docker for Windows` can also be used as the running environment under `Windows`.

The various versions of Dockerfile have been prepared for you in the [XueSiLf/easyswoole-docker](https://github.com/XueSiLf/easyswoole-docker) project, or directly based on the already built [easyswoolexuesi2021/easyswoole](https://hub.docker.com/repository/docker/easyswoolexuesi2021/easyswoole) Image to run.

When you don't want to use `Docker` as the basis for your running environment, you need to make sure that your operating environment meets the following requirements:

- PHP >= 7.4
- Swoole PHP extension >= 4.4.23 and Swoole PHP extension <= 4.4.26
- JSON PHP extension
- Pcntl PHP extension
- OpenSSL PHP extension （If you need to use the `HTTPS`）

# Installation with Composer

The easiest way to create a new `EasySwoole` project is to use [Composer](https://getcomposer.org/). If you don't have it already installed, then please install as per the [documentation](https://getcomposer.org/download/).

To create your new `EasySwoole` project:

## Install 3.5.x version

```bash
composer create-project easy-swoole-php/easyswoole-skeleton="3.5.1" project_name
```

If your development environment is based on `Docker` you can use the official `Composer` image to create a new `EasySwoole` project:

```bash
docker run --rm -it -v $(pwd):/app composer create-project --ignore-platform-reqs easy-swoole-php/easyswoole-skeleton="3.5.1" project_name
```

## Install 3.7.x version

```bash
composer create-project easy-swoole-php/easyswoole-skeleton="3.7.1" project_name
```

If your development environment is based on `Docker` you can use the official `Composer` image to create a new `EasySwoole` project:

```bash
docker run --rm -it -v $(pwd):/app composer create-project --ignore-platform-reqs easy-swoole-php/easyswoole-skeleton="3.7.1" project_name
```

# Getting started

Once installed, you can run the server immediately using the command below.

```bash
cd project_name
php easyswoole server start # for the Development environment
# php easyswoole server start -mode=dev # for the Development environment
# php easyswoole server start -mode=dev -d # for the Development environment with daemonize
# php easyswoole server start -mode=test # for the Test environment
# php easyswoole server start -mode=uat # for the User Acceptance Testing environment
# php easyswoole server start -mode=produce # for the Production environment
```

Or if in a `Docker` based environment you can use the `docker-compose/docker-compose.yml` provided by the template:

```bash
# install packages
cd project_name
docker run --rm -it -v $(pwd):/app composer install --ignore-platform-reqs

# start services
cd project_name/docker-compose
docker-compose up
```

This will start the `cli-server` on port `9501`, and bind it to all network interfaces. You can then visit the site at `http://localhost:9501/` which will bring up `EasySwoole` default home page.

# Tips

- It is recommended that you rename the project name in some files in the skeleton to your actual project name, such as files like `composer.json` and `docker-compose.yml`.
- Take a look at `App/HttpController/Index.php` to see an example of a HTTP entrypoint.

**Please Remember**: you can always replace the contents of this `README.md` file with something that fits your project description.

## Contact us

issue: [https://github.com/easy-swoole/easyswoole/issues](https://github.com/easy-swoole/easyswoole/issues)

To join the WeChat group, please add WeChat:

<img src="https://raw.githubusercontent.com/easy-swoole-php/easyswoole-skeleton/main/contactus.jpg" width="210">
