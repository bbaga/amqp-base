<?php
namespace Amqp\Base\Builder;

use Amqp\Base\Builder\Interfaces\Component;

class Queue extends Base implements Component
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var \AMQPQueue[]
     */
    protected $queues;

    /**
     * @var Channel
     */
    protected $channelBuilder;

    /**
     * Initialise
     *
     * @param array $configuration    The configuration for the queues
     * @param Channel $channelBuilder The channel builder
     */
    public function __construct($configuration, Channel $channelBuilder)
    {
        $this->configuration = $configuration;
        $this->channelBuilder = $channelBuilder;
    }

    public function get($queueName)
    {
        if (isset($this->queues[$queueName])) {
            // check if connection is still opened
            $connection = $this->queues[$queueName]->getConnection();
            if (!$connection->isConnected()) {
                $connection->connect();
            }

            return $this->queues[$queueName];
        }

        if (!isset($this->configuration[$queueName])) {
            throw new Exception('Not Found', 404);
        }

        $configuration = $this->configuration[$queueName];
        $channelName = $configuration['channel'];
        $channel = $this->channelBuilder->get($channelName);

        $queue = $this->establishQueue($configuration, $channel);

        if (isset($configuration['bindings'])) {
            $bindings = $configuration['bindings'];
            foreach ($bindings as $binding) {
                if (isset($binding['arguments'])) {
                    $arguments = $binding['arguments'];
                } else {
                    $arguments = array();
                }
                $queue->bind($binding['exchange'], $binding['routingKey'], $arguments);
            }
        }

        return $queue;
    }

    /**
     * @param array $configuration  The queue configuration array
     * @param \AMQPChannel $channel The channel on which the queue needs to be defined
     *
     * @return \AMQPQueue
     */
    public function establishQueue($configuration, \AMQPChannel $channel)
    {
        $queue = new \AMQPQueue($channel);

        // set the name only if we have the name defined. Otherwise obtain a dynamically named queue
        if ($configuration['name'] !== '') {
            $queue->setName($this->getName($configuration['name']));
        }

        $queue->setFlags($this->buildBitmask($configuration['flags']));
        if (isset($configuration['arguments'])) {
            $queue->setArguments($this->queuePropertiesTranslation($configuration['arguments']));
        }

        $queue->declareQueue();

        return $queue;
    }

    /**
     * Maps queue properties on actual supported properties in RabbitMQ
     *
     * @param array $arguments The list of arguments to map
     *
     * @return array
     */
    protected function queuePropertiesTranslation(array $arguments)
    {
        $ret = array();
        if (isset($arguments['message_ttl'])) {
            $ret['x-message-ttl'] = $arguments['message_ttl'];
        }

        if (isset($arguments['expires'])) {
            $ret['x-expires'] = $arguments['expires'];
        }

        if (isset($arguments['dl_exchange'])) {
            $ret['x-dead-letter-exchange'] = $arguments['dl_exchange'];
        }

        if (isset($arguments['dl_routingKey'])) {
            $ret['x-dead-letter-routing-key'] = $arguments['dl_routingKey'];
        }

        if (isset($arguments['max_length'])) {
            $ret['x-max-length'] = $arguments['max_length'];
        }

        if (isset($arguments['max_bytes'])) {
            $ret['x-max-length-bytes'] = $arguments['max_bytes'];
        }

        return $ret;
    }
}