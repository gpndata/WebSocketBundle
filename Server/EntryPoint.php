<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Server;

use Gos\Bundle\WebSocketBundle\Server\App\Registry\ServerRegistry;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
final class EntryPoint implements ServerLauncherInterface
{
    /**
     * @var ServerRegistry
     */
    private $serverRegistry;

    public function __construct(ServerRegistry $serverRegistry)
    {
        $this->serverRegistry = $serverRegistry;
    }

    /**
     * @throws \RuntimeException
     */
    public function launch(?string $serverName, string $host, int $port, bool $profile): void
    {
        if (null === $serverName) {
            $servers = $this->serverRegistry->getServers();

            if (empty($servers)) {
                throw new \RuntimeException('There are no servers registered to launch.');
            }

            reset($servers);
            $server = current($servers);
        } else {
            if (!$this->serverRegistry->hasServer($serverName)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unknown server %s, available servers are [ %s ]',
                        $serverName,
                        implode(', ', array_keys($this->serverRegistry->getServers()))
                    )
                );
            }

            $server = $this->serverRegistry->getServer($serverName);
        }

        $server->launch($host, $port, $profile);
    }
}
