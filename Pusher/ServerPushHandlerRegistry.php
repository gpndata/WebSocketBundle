<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Pusher;

final class ServerPushHandlerRegistry
{
    /**
     * @var ServerPushHandlerInterface[]
     */
    private $pushHandlers = [];

    public function addPushHandler(ServerPushHandlerInterface $handler): void
    {
        $this->pushHandlers[$handler->getName()] = $handler;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPushHandler(string $name): ServerPushHandlerInterface
    {
        if (!$this->hasPushHandler($name)) {
            throw new \InvalidArgumentException(sprintf('A push handler named "%s" has not been registered.', $name));
        }

        return $this->pushHandlers[$name];
    }

    /**
     * @return ServerPushHandlerInterface[]
     */
    public function getPushers(): array
    {
        return $this->pushHandlers;
    }

    public function hasPushHandler(string $name): bool
    {
        return isset($this->pushHandlers[$name]);
    }
}
