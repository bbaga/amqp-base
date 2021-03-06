<?php
require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/../config.php';

$adapter = new \Amqp\Adapter\AmqplibAdapter($config);

$msg = new \Amqp\Message();
$msg->setDeliveryMode(2);
$msg->setHeaders(['x-foo' =>'sfgsd']);
$msg->setPayload(uniqid());

for ($i = 3; $i--;) {
    $adapter->publish('global', $msg);
}

$adapter->listen(
    'debug',
    function (\Amqp\Message\MessageInterface $message, \Amqp\Message\Result $result) {
        echo $message->getPayload(),PHP_EOL;
        return $result->ack();
    },
    ['multi_ack' => true] // will acknowledge after every 5 message (prefetch_count/2)
);