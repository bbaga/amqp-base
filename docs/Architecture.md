# Architecture

The library is structured on two levels:

* the base 
* the utils

## Base

The base contains the builders and configuration parsers for the amqp components/configuration.

It uses [Symfony Config](https://github.com/symfony/Config) for validating the configuration and [Symfony Yaml](https://github.com/symfony/Yaml) for parsing it.

Also depends on [PHP AMQP extension >= 1.4](https://packagist.org/packages/pdezwart/php-amqp).

## Util

The util contains basic implementations and configuration parsers for consumers and publishers including blocking/non-blocking consumers, rpc consumers and publishers.

## Development environment

It is highly recommended to use [Symfony Dependency Injection component](https://github.com/symfony/DependencyInjection) for managing all dependencies.