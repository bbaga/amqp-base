<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class TestProcessor implements \Amqp\Util\Interfaces\Processor
{
    public function process(\AMQPEnvelope $message)
    {
        return \Amqp\Util\Interfaces\Processor::ERR_BAD_REQUEST;
    }
}

class TateTimeProcessor extends testProcessor
{
    public function process(\AMQPEnvelope $message)
    {
        echo 'Date time is: ' . $message->getBody() . PHP_EOL;
    }
}

$container   = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$fileLocator = new \Symfony\Component\Config\FileLocator(__DIR__ . '/config');
$loader      = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, $fileLocator);

$loader->load('services.yml');
$container->setParameter('config_path', __DIR__ . '/config');

$listener = $container->get('listener.test');

$listener->listen();
