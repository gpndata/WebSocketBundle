<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Event;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Johann Saunier <johann_27@hotmail.fr>
 */
class ClientRejectedEvent extends Event
{
    /**
     * @var string
     */
    protected $origin;

    /**
     * @var RequestInterface|null
     */
    protected $request;

    public function __construct(string $origin, ?RequestInterface $request = null)
    {
        $this->origin = $origin;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    public function hasRequest(): bool
    {
        return null !== $this->request;
    }
}
