# amqp-base

[![readthedocs](https://readthedocs.org/projects/amqp-base/badge/?version=latest)](http://amqp-base.readthedocs.org/en/latest/README/)
[![travis ci](https://api.travis-ci.org/c-datculescu/amqp-base.svg)](https://travis-ci.org/c-datculescu/amqp-base)

c-datculescu/amqp-base is a small library designed to interact and abstract operating a AMQP 0.9.1 compatible broker

Although the library is designed to generally work with all AMQP 0.9.1 brokers, it is specifically designed to take
advantage of RabbitMQ extensions and implementations.

## Low level components

**amqp-base** offers access to the basic AMQP components:

* connections
* channels
* queues
* exchanges
* bindings

Additionally, via the configuration options, access is offered for the following RabbitMQ extensions:

* dead-lettering - see docs at: https://www.rabbitmq.com/dlx.html
* time to live - see docs at https://www.rabbitmq.com/ttl.html
* length limit - see docs at https://www.rabbitmq.com/maxlength.html
* alternate exchanges - see docs at https://www.rabbitmq.com/ae.html
* exchange 2 exchange bindings - see docs at https://www.rabbitmq.com/e2e.html

For the future more extensions will be implemented including:

* priority queues - see docs at https://www.rabbitmq.com/priority.html
* basic.nack - see the docs at https://www.rabbitmq.com/nack.html
* sender routing - see the docs at https://www.rabbitmq.com/sender-selected.html

## High level components

**amqp-base** also offers access to consumers/publishers under the Util namespace.

The following types of consumers are offered:

* Simple consumer - simple consumer operating in blocking mode (using consume method)
* Simple non-blocking consumer - simple consumer in non-blocking mode (using get method)
* Rpc consumer - special consumer designed for RPC types of communication

In the case of publishers, there are two types of publishers offered:

* Simple publisher - just a simple publisher pushing messages
* RPC publisher - specifically designed for RPC publisher

A few properties are implemented on top of consumers:

* **Monitors** - these are special objects that are designed to detect certain conditions and stop the consumer all together:
  
  - **FileChange monitor** - monitors a file for changes in the mtime. Useful when new deploys happen. If given the choice, a combo of supervisord and monit is preferable
  - **Memory monitor** - monitors the memory currently consumed by the listener. Since php long running apps tend to leak at least some memory, it is useful to stop the listener when the limit is reached
  - **MessageCounter** - stops the listener after <x> messages.
* The simple consumer can do multi-ack, ack messages only after <x> amount of messages. This increases the performance of the delivery.

The rpc publisher allows for setting a timeout in which the operation is marked as failed.

## Configuration

The configuration is in the form of yml files, in order to allow inheritance and to reduce the verbosity

It is structured into three major areas:

* amqp
* consumer
* publisher

Sample documentation can be located in the /config directory, with all the values explained.

## Other features

Configuration enables support for dependencies between queues and exchanges, providing a way to declare an entire infrastructure in one run.

Also provides support for detecting cyclic dependencies in order to avoid running in a infinite loop when dependencies are wrongly defined.

The consumers have support for attaching processors, offering a interface for such a construct. This essentially decouples the application from the transport layer.

Queue/Exchange names can be declared in multiple ways:

* static - simple string (example: test-queue-1)
* function call - the name of the queue/exchange is retrieved by calling a function, useful when implementing certain patterns that depend for example on the hostname
* static method call - same as function, except it is a static method call
* dynamic method call - same as function, except it is a dynamic method call

# Authors

Vitaliy Stepanyuk <vstepanuyk@gmail.com>

Cristian Datculescu <cristian.datculescu@gmail.com>