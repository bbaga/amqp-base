<?php
namespace Amqp\Base\Builder;

class Connection
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var \AMQPConnection[]
     */
    protected $connections = array();

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $connectionName The name of the connection
     *
     * @return \AMQPConnection
     *
     * @throws Exception If the connection definition does not exist
     */
    public function get($connectionName)
    {
        if (isset($this->connections[$connectionName])) {
            // check if the connection is still established
            $connection = $this->checkIfConnected($this->connections[$connectionName]);
            return $connection;
        }

        // retrieve the connection information
        if (!isset($this->configuration[$connectionName])) {
            throw new Exception("Not Found", 404);
        }

        $configuration = $this->configuration[$connectionName];

        // connect_timeout does not appear to be exposed as a method via the public api, therefore needs to be passed
        // as a constructor parameter
        $tempConfig = array();
        $tempConfig['connect_timeout'] = $configuration['connectTimeout'];
        $connection = $this->establishConnection($tempConfig, $configuration);
        $this->connections[$connectionName] = $connection;

        return $connection;
    }

    /**
     * A connection can end up being close for multiple reasons:
     *      - one client can close it in a different publisher/consumer
     *      - an exception caused by declaring a invalid exchange/queue might close the connection
     *
     * @param \AMQPConnection $connection The connection
     * @return \AMQPConnection
     */
    protected function checkIfConnected(\AMQPConnection $connection)
    {
        if ($connection->isConnected()) {
            return $connection;
        }

        $connection->connect();
        return $connection;
    }

    /**
     * Establishes the connection if no connection was established before
     *
     * @param array $tempConfig    The temporary config for the constructor
     * @param array $configuration The configuration for the connection
     *
     * @return \AMQPConnection
     */
    protected function establishConnection($tempConfig, $configuration)
    {
        // initialize the connection
        $connection = new \AMQPConnection($tempConfig);

        $hosts = $configuration['host'];
        // choose random host to simulate "load balancing"
        $host = $hosts[array_rand($hosts)];
        $connection->setHost($host);

        $connection->setPort($configuration['port']);
        $connection->setLogin($configuration['login']);
        $connection->setPassword($configuration['password']);
        $connection->setVhost($configuration['vhost']);
        $connection->setReadTimeout($configuration['readTimeout']);
        $connection->setWriteTimeout($configuration['writeTimeout']);
        $connection->connect();
        return $connection;
    }
}