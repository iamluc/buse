<?php

namespace Buse\Console\Formatter;

class Spinner implements FormatterInterface
{
    protected $output;
    protected $message;
    protected $messageFinish;
    protected $exitCode;

    protected $steps = ['-', '\\', '|', '/'];
    protected $current = 0;
    protected $finished = false;

    public function __construct($message = '', $messageFinish = null)
    {
        $this->message = $message;
        $this->messageFinish = $messageFinish;
    }

    public function display($maxLength = null)
    {
        $step = $this->current++ % count($this->steps);
        $spinner = '';
        if (!$this->finished) {
            $spinner = $this->steps[$step].' ';
        }

        if (null === $this->exitCode) {
            $tags = ['', ''];
        } elseif (0 === $this->exitCode) {
            $tags = ['<fg=green>', '</fg=green>'];
        } else {
            $tags = ['<fg=red>', '</fg=red>'];
        }

        $str = trim(preg_replace('/\s+/', ' ', $this->message));

        if ($maxLength) {
            $str = substr($str, 0, $maxLength - strlen($spinner) - strlen($tags[0]) - strlen($tags[1]));
        }

        return $spinner.$tags[0].$str.$tags[1];
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function finish($exitCode)
    {
        if (0 === $exitCode && null !== $this->messageFinish) {
            $this->message = $this->messageFinish;
        }

        $this->finished = true;
        $this->exitCode = $exitCode;
    }
}
