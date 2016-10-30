<?php
/**
 * Reports 'now playing' on an IceCast stream and collects votes (http://www.glump.net/software/pintsize)
 *
 * @link TODO for the canonical source repository
 * @copyright Copyright (c) 2016 Brendan Kidwell (http://www.glump.net)
 * @license https://www.gnu.org/licenses/gpl.html GPL version 3
 * @package Pintsize\Phergie\PintsizePlugin
 */

namespace Pintsize\IRC;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Bot\React\EventEmitterAwareInterface;
use Phergie\Irc\Event\UserEventInterface;
use Phergie\Irc\Event\ServerEventInterface;
use Phergie\Irc\Event\EventInterface;
use Pintsize\Models\Channel as Channel;
use Pintsize\Common\Config as Config;
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
    private $ignoredNicks = [];
    private $authedNicks = [];
    private $pendingCmd = [];
    private $queue;

    public function __construct(array $config = [])
    {
        foreach (Config::get('channels') as $c) {
            $channel = new Channel($c['channel'], $c['announcemode']);
            $this->channels[$c['channel']] = $channel;
        }
        foreach (Config::get('ignoredNicks') as $i) {
            $this->ignoredNicks[] = $i;
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
            'pintsize.ready' => 'onReady',
            'irc.received.privmsg' => 'onPrivmsg',
            'irc.received.quit' => 'onQuit',
            'irc.received.each' => 'onReceivedEach',
            'irc.received.rpl_endofwhois' => 'onWhoisDone',
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
            if (!isset($channel)) {
                return;
            }
            $channel->joined = true;
            $this->logger->debug("onJoin: bot has joined {$channel->name}");
            $this->checkJoinedall($queue);
        }
    }

    public function onQuit(UserEventInterface $event, Queue $queue)
    {
        $nick = $event->getNick();
        if (array_key_exists($nick, $this->authedNicks)) {
            unset($this->authedNicks[$nick]);
        }
    }

    public function onReady(Queue $queue)
    {
        $this->queue = $queue;
        $this->say('Hello world.');
    }

    public function onPrivmsg(UserEventInterface $event, Queue $queue)
    {
        if ($this->isNickIgnored($event)) {
            return;
        }
        $nick = $event->getNick();
        $params = $event->getParams();
        $this->logger->debug('onPrivMsg', $params);
        $receiver = $params['receivers'];
        $text = $params['text'];
        $private = $this->isChannelName($receiver);

        if ($text == '!test') {
            $this->checkAuth($nick, $receiver, function ($params) {
                $nick = $params['nick'];
                $replyTo = $params['replyTo'];
                $authed = $params['authed'];
                $this->reply("$nick is authed? $authed", $replyTo, $nick);
            });
        }
    }

    private function checkAuth($nick, $replyTo, $callback)
    {
        $this->logger->debug("Checking auth for $nick");
        if (array_key_exists($nick, $this->authedNicks)) {
            $callback(array(
                'nick' => $nick,
                'replyTo' => $replyTo,
                'authed' => $this->authedNicks[$nick]
            ));
            return;
        }
        $this->pendingCmd[$nick] = array(
            'params' => array(
                'nick' => $nick,
                'replyTo' => $replyTo,
                'authed' => null
            ),
            'timestamp' => time(),
            'callback' => $callback
        );
        $this->logger->debug("Sent WHOIS $nick");
        $this->queue->ircWhois($nick);
    }

    public function onReceivedEach(EventInterface $event, Queue $queue)
    {
        if (method_exists($event, 'getCode')) {
            if ($event->getCode() == 307) {
                $params = $event->getParams();
                $nick = $params[1];
                $this->authedNicks[$nick] = true;
            }
        }
    }

    public function onWhoisDone(ServerEventInterface $event, Queue $queue)
    {
        $params = $event->getParams();
        $nick = $params[1];
        if (!array_key_exists($nick, $this->authedNicks)) {
            $this->authedNicks[$nick] = false;
        }
        if (array_key_exists($nick, $this->pendingCmd)) {
            $cmd = $this->pendingCmd[$nick];
            $callback = $cmd['callback'];
            $params = $cmd['params'];
            unset($this->pendingCmd[$nick]);

            $params['authed'] = $this->authedNicks[$nick];
            $callback($params);
        }

        // Cleanup
        $now = time();
        $this->pendingCmd = array_filter($this->pendingCmd, function ($value) {
            // Discard over 1 minute old
            return ($now - $value->timestamp < 60);
        });
    }


    private function say($message, Channel $channel = null)
    {
        if (isset($channel)) {
            $list = array($channel);
        } else {
            $list =& $this->channels;
        }
        foreach ($list as $item) {
            if (!$item->joined) {
                continue;
            }
            if ($item->announcemode == Channel::MODE_NOTICE) {
                $this->queue->ircNotice($item->name, $message);
            } elseif ($item->announcemode == Channel::MODE_PRIVMSG) {
                $this->queue->ircPrivmsg($item->name, $message);
            }
        }
    }
    private function reply($message, $receiver, $nick)
    {
        if ($this->isChannelName($receiver)) {
            $channel = $this->channels[$receiver];
            if (isset($channel)) {
                $this->say($message, $channel);
            }
        } else {
            $this->queue->ircPrivmsg($nick, $message);
        }
    }

    private function checkJoinedall(Queue $queue)
    {
        if ($this->joinedall) {
            return;
        }
        foreach ($this->channels as $channel) {
            if ($channel->joined == false) {
                return;
            }
        }
        $this->emitter->emit('pintsize.ready', array($queue));
    }

    private function isNickIgnored(UserEventInterface $event)
    {
        $nick = $event->getNick();
        if ($nick == $event->getConnection()->getNickname()) {
            return true;
        }
        if (in_array($nick, $this->ignoredNicks)) {
            return true;
        }
    }

    private function isChannelName($name)
    {
        return preg_match('/^#/', $name);
    }
}
