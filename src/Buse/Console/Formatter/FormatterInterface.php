<?php

namespace Buse\Console\Formatter;

interface FormatterInterface
{
    public function display($maxLength = null);

    public function setMessage($message);

    public function finish($exitCode);
}
