<?php

namespace Amqp\Consumer;

interface ConsumerInterface
{
    /**
     * @param string $queue
     * @param callable $callback
     * @param array $options
     * @return void
     */
    public function listen($queue, callable $callback, array $options = array());
}