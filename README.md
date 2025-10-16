# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Compile
``php -S localhost:8000 -t public``

storage link
``ln -s ../storage/app/public/uploads uploads``

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Docker

docker run -d --name sign-gateway \
-p 1303:1303 \
-e API_URL={{API_URL}} \
-e API_KEY=your-api-key \
-e ENV=STAGING registry.xignature.co.id/xignature/public-sign-gateway:2.1.0


## SAMPLE DOCKER

https://docs.xignature.dev/en/api-v3/sign-gateway

docker login registry.xignature.co.id -u public -p CS0BkvzAytj9yxj
docker login registry.xignature.dev -u public -p CS0BkvzAytj9yxj


docker run -d --name sign-gateway \
-p 1303:1303 \
-e API_URL=https://api.xignature.dev \
-e API_KEY=71645d4293da14a0e8e9098c27b60344be4afc14e42a1bf4967f929f1e4c19f2f8ed3463f4ea1f2228b8ed007bad2e65a438131a86e0105baec99bb082459a89 \
-e ENV=STAGING registry.xignature.co.id/xignature/public-sign-gateway:2.1.0

docker run -d --name sign-gateway -p 1303:1303 -e API_URL=https://api.xignature.dev -e API_KEY=71645d4293da14a0e8e9098c27b60344be4afc14e42a1bf4967f929f1e4c19f2f8ed3463f4ea1f2228b8ed007bad2e65a438131a86e0105baec99bb082459a89 -e ENV=STAGING registry.xignature.co.id/xignature/public-sign-gateway:2.1.0