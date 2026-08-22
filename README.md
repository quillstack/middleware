# Quillstack Middleware

[![Tests](https://github.com/quillstack/middleware/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/middleware/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/middleware.svg)](https://packagist.org/packages/quillstack/middleware)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/middleware.svg)](https://packagist.org/packages/quillstack/middleware)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/middleware)](https://packagist.org/packages/quillstack/middleware)
[![StyleCI](https://github.styleci.io/repos/304422648/shield?branch=main)](https://github.styleci.io/repos/304422648?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/quillstack/middleware/badge)](https://www.codefactor.io/repository/github/quillstack/middleware)
[![Maintainability](https://api.codeclimate.com/v1/badges/8605086862df3345be8e/maintainability)](https://codeclimate.com/github/quillstack/middleware/maintainability)
[![License](https://img.shields.io/packagist/l/quillstack/middleware)](https://github.com/quillstack/middleware/blob/main/LICENSE)

The middleware library based on PSR-15: HTTP Server Request Handlers.

### Unit tests

Run tests using a command:

```
phpdbg -qrr ./vendor/bin/unit-tests
```

### Docker

```shell
$ docker-compose up -d
$ docker exec -w /var/www/html -it quillstack_middleware sh
```
