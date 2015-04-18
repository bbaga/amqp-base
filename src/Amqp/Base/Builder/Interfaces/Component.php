<?php
namespace Amqp\Base\Builder\Interfaces;

interface Component
{
    /**
     * Returns one of the four base components of AMQP:
     *  queue
     *  exchange
     *  channel
     *  connection
     *
     * @param string $name The name for the specified component
     *
     * @return \AMQPQueue|\AMQPExchange|\AMQPChannel|\AMQPConnection
     */
    public function get($name);
}