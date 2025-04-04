<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Event;

use Gos\Bundle\WebSocketBundle\Client\Auth\WebsocketAuthenticationProviderInterface;
use Gos\Bundle\WebSocketBundle\Client\ClientStorageInterface;
use Gos\Bundle\WebSocketBundle\Client\Exception\ClientNotFoundException;
use Gos\Bundle\WebSocketBundle\Client\Exception\StorageException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
final class ClientEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param ClientStorageInterface
     */
    private $clientStorage;

    /**
     * @var WebsocketAuthenticationProviderInterface
     */
    private $authenticationProvider;

    public function __construct(
        ClientStorageInterface $clientStorage,
        WebsocketAuthenticationProviderInterface $authenticationProvider
    ) {
        $this->clientStorage = $clientStorage;
        $this->authenticationProvider = $authenticationProvider;
    }

    public function onClientConnect(ClientEvent $event): void
    {
        $this->authenticationProvider->authenticate($event->getConnection());
    }

    public function onClientDisconnect(ClientEvent $event): void
    {
        $conn = $event->getConnection();
        $storageId = $this->clientStorage->getStorageId($conn);

        $loggerContext = [
            'connection_id' => $conn->resourceId,
            'session_id' => $conn->WAMP->sessionId,
            'storage_id' => $storageId,
        ];

        try {
            if ($this->clientStorage->hasClient($storageId)) {
                $token = $this->clientStorage->getClient($storageId);

                $this->clientStorage->removeClient($storageId);

                $username = $token->getUsername();

                if ($this->logger) {
                    $this->logger->info(
                        sprintf('%s disconnected', $username),
                        array_merge(
                            $loggerContext,
                            ['username' => $username]
                        )
                    );
                }
            }
        } catch (ClientNotFoundException $e) {
            if ($this->logger) {
                $this->logger->info(
                    'User timed out',
                    array_merge(
                        $loggerContext,
                        ['exception' => $e]
                    )
                );
            }
        } catch (StorageException $e) {
            if ($this->logger) {
                $this->logger->info(
                    'Error processing user in storage',
                    array_merge(
                        $loggerContext,
                        ['exception' => $e]
                    )
                );
            }
        }
    }

    public function onClientError(ClientErrorEvent $event): void
    {
        if (!$this->logger) {
            return;
        }

        $conn = $event->getConnection();
        $e = $event->getException();

        $loggerContext = [
            'connection_id' => $conn->resourceId,
            'session_id' => $conn->WAMP->sessionId,
            'exception' => $e,
        ];

        $storageId = $this->clientStorage->getStorageId($conn);

        if ($this->clientStorage->hasClient($storageId)) {
            $token = $this->clientStorage->getClient($storageId);

            $loggerContext['client'] = $token->getUsername();
        }

        $this->logger->error(
            'Connection error',
            $loggerContext
        );
    }

    public function onClientRejected(ClientRejectedEvent $event): void
    {
        if ($this->logger) {
            $this->logger->warning(
                'Client rejected, bad origin',
                [
                    'origin' => $event->getOrigin(),
                ]
            );
        }
    }
}
