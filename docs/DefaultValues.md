# Default values

Most components exposed by the library have default values. Below are listed the default values as well as an explanation 
for them.

## Connection

**host** - defaults to array(localhost) - generally useful to run through the examples locally.

**port** - defaults to 5672. The cases in which the default port changes are very rare, so it is a quite safe default.

**login** - defaults to *guest* . This user is available only on localhost as of RabbitMQ 3.3 and is the default user.

**password** - defaults to *guest* . In combination with the guest user this is available only on localhost.

**vhost** - defaults to */* . The */* is the default vhost on all the RabbitMQ installations.

**readTimeout** - the interval in which if no message is received the consumer will exit. Set to 0 for never exiting. It is recommended that it is kept at a default decent value because it avoids issues created by half-open tcp sockets.

**writeTimeout** - the interval in which the broker has to answer to a publishing request. 200 ms is a decent default, as 200 ms might indicate a network issue. Set to 0 to eliminate the default.

**connectTimeout** - the interval in which the broker has to answer to a connection request. 200 ms or more might indicate a network issue.

## Channel

**count** - the prefetch count. Usually 100 is a good prefetch count for a stable set of consumers.

**transactional** - defaults to false. Generally transactions should be avoided

## Exchange

**flags** - defaults to array(AMQP_DURABLE). It is a sensible default in case the broker goes down.

**type** - defaults to AMQP_EX_TYPE_TOPIC since most exchanges would be topics anyways.

**name.type** - defaults to *constant*. Generally the exchange name will be constant.

## Queue

**flags** - defaults to array(AMQP_DURABLE). It is a sensible default in case the broker goes down.

**name.type** - defaults to *constant*. Generally the exchange name will be constant.

## Publisher

**timeout** - defaults to 0 (aka. always waiting). It is useful only for RPC communication.

## Consumer

**onProcessorError** - defaults to continue. This would cause the listener to keep processing messages even if the processor failed.

**bulkAck** - defaults to 0, basically acknowledging every single message. Although not a very performant behavior, enabling this by default might create confusion and unwanted effects.