<?php
namespace Amqp\Base\Builder;

use Amqp\Base\Builder\Interfaces\Component;
use Symfony\Component\Config\Definition\Exception\Exception;

class Exchange extends Base implements Component
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var \AMQPExchange[]
     */
    protected $exchanges = array();

    /**
     * @var Channel
     */
    protected $channelBuilder;

    /**
     * Initialize the exchange builder
     *
     * @param array $configuration    The configuration for all exchanges
     * @param Channel $channelBuilder The channel builder
     */
    public function __construct(array $configuration, Channel $channelBuilder)
    {
        $this->configuration = $configuration;
        $this->channelBuilder = $channelBuilder;
    }

    /**
     * Returns an exchange to be used
     *
     * @param string $exchangeName The name of the exchange as defined in the configuration
     *
     * @return \AMQPExchange
     *
     * @throws Exception If the exchange definition cannot be located
     */
    public function get($exchangeName)
    {
        if (isset($this->exchanges[$exchangeName])) {
            // check if the connection is still up
            $connection = $this->exchanges[$exchangeName]->getConnection();
            if (!$connection->isConnected()) {
                $connection->connect();
            }

            return $this->exchanges[$exchangeName];
        }

        // initialize the exchange
        if (!isset($this->configuration[$exchangeName])) {
            throw new Exception('Not Found', 404);
        }

        $configuration = $this->configuration[$exchangeName];
        $channelName = $configuration['channel'];
        $exchange = $this->establishExchange($channelName, $configuration);
        $this->exchanges[$exchangeName] = $exchange;

        // get the bindings and apply them
        if (isset($configuration['bindings'])) {
            $bindings = $configuration['bindings'];
            foreach ($bindings as $binding) {
                $exchange->bind($binding['exchange'], $binding['routingKey']);
            }
        }

        return $exchange;
    }

    /**
     * @param string $channelName  The channel on which the current exchange will be declared
     * @param array $configuration The configuration for the current exchange
     *
     * @return \AMQPExchange
     *
     * @throws \Exception if an exception was thrown before
     */
    public function establishExchange($channelName, $configuration)
    {
        $channel = $this->channelBuilder->get($channelName);
        $exchange = new \AMQPExchange($channel);

        // retrieve the default exchange
        if (isset($configuration['isDefault'])) {
            $exchange->setName('');
            $this->exchanges['default'] = $exchange;

            return $exchange;
        }

        if (isset($configuration['name'])) {
            $exchange->setName($this->getName($configuration['name']));
        }

        if (isset($configuration['type'])) {
            $exchange->setType($this->getConstant($configuration['type']));
        }

        if (isset($configuration['flags'])) {
            $exchange->setFlags($this->buildBitmask($configuration['flags']));
        }

        // alternate exchange
        if (isset($configuration['ae'])) {
            if (!isset($configuration['arguments'])) {
                $configuration['arguments'] = array();
            }
            $configuration['arguments']['alternate-exchange'] = $configuration['ae'];
        }

        if (isset($configuration['arguments'])) {
            $exchange->setArguments($configuration['arguments']);
        }

        $exchange->declareExchange();
        return $exchange;
    }
}