<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Tests\Event;

use Gos\Bundle\WebSocketBundle\Event\ServerEvent;
use Gos\Bundle\WebSocketBundle\Event\StartServerListener;
use Gos\Bundle\WebSocketBundle\Pusher\ServerPushHandlerRegistry;
use Gos\Bundle\WebSocketBundle\Server\App\Registry\PeriodicRegistry;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use React\Socket\ServerInterface;

class StartServerListenerTest extends TestCase
{
    /**
     * @var PeriodicRegistry
     */
    private $periodicRegistry;

    /**
     * @var ServerPushHandlerRegistry
     */
    private $serverPushHandlerRegistry;

    /**
     * @var StartServerListener
     */
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->periodicRegistry = new PeriodicRegistry();
        $this->serverPushHandlerRegistry = new ServerPushHandlerRegistry();

        $this->listener = new StartServerListener($this->periodicRegistry, $this->serverPushHandlerRegistry);
    }

    /**
     * @requires extension pcntl
     */
    public function testTheUserIsAuthenticatedWhenTheClientConnectEventIsDispatched()
    {
        $loop = $this->createMock(LoopInterface::class);
        $loop->expects($this->once())
            ->method('addSignal');

        $event = $this->createMock(ServerEvent::class);
        $event->expects($this->once())
            ->method('getEventLoop')
            ->willReturn($loop);

        $event->expects($this->once())
            ->method('getServer')
            ->willReturn($this->createMock(ServerInterface::class));

        $this->listener->bindPnctlEvent($event);
    }
}
