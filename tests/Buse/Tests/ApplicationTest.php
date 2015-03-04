<?php

namespace Buse\Tests;

use Buse\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testApplicationName()
    {
        $application = new Application();

        $this->assertSame('Buse', $application->getName());
    }

    public function testApplicationCommands()
    {
        $application = new Application();

        $this->assertTrue($application->has('config'));
        $this->assertTrue($application->has('status'));
        $this->assertTrue($application->has('fetch'));
        $this->assertTrue($application->has('pull'));
        $this->assertTrue($application->has('push'));
        $this->assertTrue($application->has('exec'));
        $this->assertTrue($application->has('tag'));
    }
}
