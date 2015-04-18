<?php
/**
 * Class capable of returning any of the components defined in the amqp configuration file
 * The components are cached so that we do not create a connection/channel/queue/exchange every time
 * but rather reuse the currently existing ones that have been defined already.
 *
 * @author Cristian Datculescu <cristian.datculescu@gmail.com>
 */
namespace Amqp\Base\Builder;

use Amqp\Base\Config\Processor;

class Amqp implements Interfaces\Amqp
{
    /**
     * @var array
     */
    protected $amqpConfiguration = array();

    /**
     * Registers all the unresolved dependencies, looking for dependencies which are cyclic
     * @var array
     */
    protected $cyclicLoggers = array(
        'queues'    => array(),
        'exchanges' => array(),
    );

    /**
     * @var Connection
     */
    protected $connectionBuilder;

    /**
     * @var Channel
     */
    protected $channelBuilder;

    /**
     * @var Queue
     */
    protected $queueBuilder;

    /**
     * @var Exchange
     */
    protected $exchangeBuilder;

    /**
     * @var \Amqp\Base\Builder\Interfaces\Component[]
     */
    protected $builders = array();

    /**
     * @param Processor $configFactory
     */
    public function __construct(Processor $configFactory)
    {
        $this->amqpConfiguration = $configFactory->getDefinition(new \Amqp\Base\Config\Amqp());
        $this->connectionBuilder    = new Connection($this->amqpConfiguration['connection']);
        $this->channelBuilder       = new Channel($this->amqpConfiguration['channel'], $this->connectionBuilder);
        $this->queueBuilder         = new Queue($this->amqpConfiguration['queue'], $this->channelBuilder);
        $this->exchangeBuilder      = new Exchange($this->amqpConfiguration['exchange'], $this->channelBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function connection($connectionName)
    {
        $connection = $this->connectionBuilder->get($connectionName);
        return $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function channel($channelName)
    {
        $channel = $this->channelBuilder->get($channelName);
        return $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($queueName, $initDependencies = true)
    {
        $configuration = $this->amqpConfiguration['queue'][$queueName];

        $needDependencies = false;
        if (isset($configuration['dependencies']) && $initDependencies == true) {
            $needDependencies = true;
            $this->checkDependencies($queueName, 'queue', $configuration['dependencies']);
        }

        $queue = $this->queueBuilder->get($queueName);

        if ($needDependencies == true) {
            $this->decreaseRefcount($queueName, 'queue');
        }

        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function exchange($exchangeName, $initDependencies = true)
    {
        $configuration = $this->amqpConfiguration['exchange'][$exchangeName];

        $needDependencies = false;
        if (isset($configuration['dependencies']) && $initDependencies == true) {
            $needDependencies = true;
            $this->checkDependencies($exchangeName, 'exchange', $configuration['dependencies']);
        }
        
        if ($needDependencies == true) {
            $this->decreaseRefcount($exchangeName, 'exchange');
        }

        return $exchange;
    }

    protected function decreaseRefcount($name, $type)
    {
        switch ($type) {
            case 'exchange':
                $key = 'exchanges';
                break;
            case 'queue':
                $key = 'queues';
                break;
            default:
                return;
        }

        if (isset($this->cyclicLoggers[$key][$name])) {
            $this->cyclicLoggers[$key][$name]--;
        }
    }

    protected function checkDependencies($name, $type, array $dependencies)
    {
        switch($type) {
            case 'exchange':
                $key = 'exchanges';
                break;
            case 'queue':
                $key = 'queues';
                break;
            default:
                return;
        }

        if (!isset($this->cyclicLoggers[$key][$name])) {
            $this->cyclicLoggers[$key][$name] = 1;
        } else {
            $this->cyclicLoggers[$key][$name]++;
        }

        $refCount = $this->cyclicLoggers[$key][$name];
        if ($refCount > 1) {
            throw new Exception('Cyclic Dependency', 500);
        }
        $this->initDependencies($dependencies);
    }

    /**
     * Initializes the dependencies
     *
     * @param array $dependencies The array of queues/exchanges dependencies
     */
    protected function initDependencies(array $dependencies)
    {
        if (isset($dependencies['exchange'])) {
            foreach ($dependencies['exchange'] as $exchange) {
                $this->exchange($exchange);
            }
        }

        if (isset($dependencies['queue'])) {
            foreach ($dependencies['queue'] as $queue) {
                $this->queue($queue);
            }
        }
    }
}
