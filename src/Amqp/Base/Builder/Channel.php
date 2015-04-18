<?php
namespace Amqp\Base\Builder;

use Amqp\Base\Builder\Interfaces\Component;

class Channel implements Component
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var \AMQPChannel[]
     */
    protected $channels;

    /**
     * @var Connection
     */
    protected $connectionBuilder;

    /**
     * Initialize the builder
     *
     * @param array $configuration          The configuration array
     * @param Connection $connectionBuilder The connection builder to be used for creating/retrieving connections
     */
    public function __construct(array $configuration, Connection $connectionBuilder)
    {
        $this->configuration        = $configuration;
        $this->connectionBuilder    = $connectionBuilder;
    }

    /**
     * @param string $channelName The channel name as defined in the configuration
     *
     * @return \AMQPChannel
     *
     * @throws Exception If the configuration could not be located for the current channel
     */
    public function get($channelName)
    {
        if (isset($this->channels[$channelName])) {
            // check if the connection is opened and if not reopen it
            $connection = $this->channels[$channelName]->getConnection();
            if (!$connection->isConnected()) {
                $connection->connect();
            }

            // if the connection is dead, than the channel is also dead
            if ($this->channels[$channelName]->isConnected() == true) {
                return $this->channels[$channelName];
            }
        }

        // retrieve the connection information
        if (!isset($this->configuration[$channelName])) {
            throw new Exception("Not Found", 404);
        }

        $configuration = $this->configuration[$channelName];
        $connectionName = $configuration['connection'];
        $connection = $this->connectionBuilder->get($connectionName);

        $channel = $this->establishChannel($configuration, $connection);
        $this->channels[$channelName] = $channel;

        return $channel;
    }

    /**
     * Establishes a channel
     *
     * @param array $configuration        The configuration for the current channel
     * @param \AMQPConnection $connection The broker connection
     *
     * @return \AMQPChannel
     *
     * @throws Exception
     */
    public function establishChannel($configuration, \AMQPConnection $connection)
    {
        $channel = new \AMQPChannel($connection);

        if (isset($configuration['count']) && isset($configuration['size'])) {
            $channel->qos($configuration['size'], $configuration['count']);
            return $channel;
        } else {
            if (isset($configuration['count'])) {
                $channel->setPrefetchCount($configuration['count']);
            }
            if (isset($configuration['size'])) {
                $channel->setPrefetchSize($configuration['size']);
                return $channel;
            }
            return $channel;
        }
    }
}