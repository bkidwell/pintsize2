<?php
namespace Pintsize\Models;

class Channel
{
    const MODE_NOTICE = 'notice';
    const MODE_PRIVMSG = 'privmsg';
    
    public $name;
    public $announcemode;
    public $joined;
    
    public function __construct($name, $announcemode)
    {
        $this->name = $name;
        $this->announcemode = $announcemode;
        $this->joined = false;
    }
}