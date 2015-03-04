<?php

namespace Buse;

use Pimple\Container;

interface ContainerAwareInterface
{
    public function setContainer(Container $container);
}
