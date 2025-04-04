<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Pusher\Zmq;

use Gos\Bundle\WebSocketBundle\Pusher\AbstractPusher;
use Gos\Bundle\WebSocketBundle\Pusher\Message;
use Gos\Bundle\WebSocketBundle\Router\WampRouter;
use Symfony\Component\Serializer\SerializerInterface;

final class ZmqPusher extends AbstractPusher
{
    /**
     * @var \ZMQSocket
     */
    private $connection;

    /**
     * @var ZmqConnectionFactoryInterface
     */
    private $connectionFactory;

    public function __construct(
        WampRouter $router,
        SerializerInterface $serializer,
        ZmqConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct($router, $serializer);

        $this->connectionFactory = $connectionFactory;
    }

    protected function doPush(Message $message, array $context): void
    {
        if (false === $this->isConnected()) {
            $this->connection = $this->connectionFactory->createConnection();
            $this->connection->connect($this->connectionFactory->buildConnectionDsn());
            $this->setConnected();
        }

        $this->connection->send($this->serializer->serialize($message, 'json'));
    }

    public function close(): void
    {
        if (false === $this->isConnected()) {
            return;
        }

        $this->connection->disconnect($this->connectionFactory->buildConnectionDsn());
    }
}
