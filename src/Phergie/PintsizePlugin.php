<?php
/**
 * Reports 'now playing' on an IceCast stream and collects votes (http://www.glump.net/software/pintsize)
 *
 * @link TODO for the canonical source repository
 * @copyright Copyright (c) 2016 Brendan Kidwell (http://www.glump.net)
 * @license https://www.gnu.org/licenses/gpl.html GPL version 3
 * @package Pintsize\Phergie\PintsizePlugin
 */

namespace Pintsize\Phergie;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\EventInterface as Event;

/**
 * Plugin class.
 *
 * @category Pintsize
 * @package Pintsize\Phergie\PintsizePlugin
 */
class PintsizePlugin extends AbstractPlugin
{
    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     *
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {

    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'irc.' => 'handleEvent',
        ];
    }

    /**
     *
     *
     * @param \Phergie\Irc\Event\EventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleEvent(Event $event, Queue $queue)
    {
    }
}
