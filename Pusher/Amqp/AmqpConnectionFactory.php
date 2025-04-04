<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Pusher\Amqp;

use Gos\Bundle\WebSocketBundle\Pusher\Exception\PusherUnsupportedException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AmqpConnectionFactory implements AmqpConnectionFactoryInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $this->resolveConfig($config);
    }

    public function createConnection(): \AMQPConnection
    {
        if (!$this->isSupported()) {
            throw new PusherUnsupportedException('The AMQP pusher requires the PHP amqp extension.');
        }

        return new \AMQPConnection($this->config);
    }

    public function createExchange(\AMQPConnection $connection): \AMQPExchange
    {
        $exchange = new \AMQPExchange(new \AMQPChannel($connection));
        $exchange->setName($this->config['exchange_name']);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange;
    }

    public function createQueue(\AMQPConnection $connection): \AMQPQueue
    {
        $queue = new \AMQPQueue(new \AMQPChannel($connection));
        $queue->setName($this->config['queue_name']);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        return $queue;
    }

    public function isSupported(): bool
    {
        return \extension_loaded('amqp');
    }

    private function resolveConfig(array $config): array
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired(
            [
                'host',
                'port',
                'login',
                'password',
            ]
        );

        $resolver->setDefaults(
            [
                'vhost' => '/',
                'read_timeout' => 0,
                'write_timeout' => 0,
                'connect_timeout' => 0,
                'queue_name' => 'gos_websocket',
                'exchange_name' => 'gos_websocket_exchange',
            ]
        );

        $resolver->setAllowedTypes('host', 'string');
        $resolver->setAllowedTypes('port', ['string', 'integer']);
        $resolver->setAllowedTypes('login', 'string');
        $resolver->setAllowedTypes('password', 'string');
        $resolver->setAllowedTypes('vhost', 'string');
        $resolver->setAllowedTypes('read_timeout', 'integer');
        $resolver->setAllowedTypes('write_timeout', 'integer');
        $resolver->setAllowedTypes('connect_timeout', 'integer');
        $resolver->setAllowedTypes('queue_name', 'string');
        $resolver->setAllowedTypes('exchange_name', 'string');

        return $resolver->resolve($config);
    }
}
