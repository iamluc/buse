<?php

namespace Buse;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class Config
{
    protected $path;
    protected $config;
    protected $accessor;

    public function __construct($path, $filename)
    {
        $this->path = realpath($path).'/'.$filename;
        $this->config = $this->readConfig($this->path);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function set($property, $value)
    {
        $this->accessor->setValue($this->config, $this->toArrayProperty($property), $value);

        return $this;
    }

    public function get($property)
    {
        return $this->accessor->getValue($this->config, $this->toArrayProperty($property));
    }

    private function readConfig($path)
    {
        if (file_exists($path)) {
            return Yaml::parse(file_get_contents($path));
        }

        return [];
    }

    private function toArrayProperty($property)
    {
        $paths = explode('.', $property);
        array_walk($paths, function (&$item, $key) { $item = '['.$item.']'; });

        return implode('', $paths);
    }

    private function writeConfig($path, $config)
    {
        file_put_contents($path, Yaml::dump($config));
        chmod($path, 0600);
    }

    public function __destruct()
    {
        if (count($this->config)) {
            $this->writeConfig($this->path, $this->config);
        }
    }
}
