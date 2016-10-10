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
use Phergie\Irc\Bot\React\EventEmitterAwareInterface;
use Phergie\Irc\Event\UserEventInterface;
use Phergie\Irc\Event\EventInterface;
use Pintsize\Models\Channel as Channel;
use Pintsize\Config as Config;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Evenement\EventEmitterInterface;

/**
 * Plugin class.
 *
 * @category Pintsize
 * @package Pintsize\Phergie\PintsizePlugin
 */
class PintsizePlugin extends AbstractPlugin implements LoggerAwareInterface, EventEmitterAwareInterface
{
    private $channels;
    protected $logger;
    private $joinedall = false;
    protected $emitter;
    
    public function __construct(array $config = [])
    {
        foreach(Config::get('channels') as $c) {
            $channel = new Channel($c['channel'], $c['announcemode']);
            $this->channels[$c['channel']] = $channel;
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function setEventEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }
    
    public function getSubscribedEvents()
    {
        return [
            'irc.received.join' => 'onJoin',
            'pintsize.joinedall' => 'onReady',
            //'irc.received.part' => 'onPart',
            //'irc.received.kick' => 'onKick',
        ];
    }

    public function onJoin(UserEventInterface $event, Queue $queue)
    {
        $nick = $event->getNick();
        $myNick = $event->getConnection()->getNickname();
        $channelName = $event->getSource();
        $this->logger->debug("onJoin:\n  nick: {$nick}\n  channel: {$channelName}\n  my nick: {$myNick}");
        if ($nick == $myNick) {
            $channel = $this->channels[$channelName];
            if(!isset($channel)) { return; }
            $channel->joined = true;
            $this->logger->debug("onJoin: bot has joined {$channel->name}");
            $this->checkJoinedall($queue);
        }
    }

    public function onReady(Queue $queue)
    {
        $this->say('Hello world.', $queue);
    }
    
    private function say($message, Queue $queue, Channel $channel = null)
    {   
        if(isset($channel)) {
            $list = array($channel);
        } else {
            $list =& $this->channels;
        }
        foreach($list as $item) {
            if(!$item->joined) { continue; }
            if($item->announcemode == Channel::MODE_NOTICE) {
                $queue->ircNotice($item->name, $message);
            } elseif($item->announcemode == Channel::MODE_PRIVMSG) {
                $queue->ircPrivmsg($item->name, $message);
            }
        }
    }
    
    private function checkJoinedall(Queue $queue)
    {
        if($this->joinedall) { return; }
        foreach($this->channels as $channel) {
            if($channel->joined == false) { return; }
        }
        $this->emitter->emit('pintsize.joinedall', array($queue));
    }
}
