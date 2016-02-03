<?php

namespace Buse;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class Config
{
    protected $path;
    protected $config;
    protected $accessor;
    protected $changed;

    public function __construct($path, $filename)
    {
        $this->path = realpath($path).'/'.$filename;
        $this->config = $this->readConfig($this->path);
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->changed = false;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function set($property, $value)
    {
        $this->changed = true;
        $this->accessor->setValue($this->config, $this->toArrayProperty($property), $value);

        return $this;
    }

    public function get($property)
    {
        return $this->accessor->getValue($this->config, $this->toArrayProperty($property));
    }

    public function getGroupsConfig(array $groups = null)
    {
        return array_filter($this->config, function ($name) use ($groups) {
            if ('global' === $name) {
                return false;
            }

            if ($groups && !in_array($name, $groups)) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);
    }

    public function hasGroup($group)
    {
        return isset($this->config[$group]);
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
        file_put_contents($path, Yaml::dump($config, 10));
        chmod($path, 0600);
    }

    public function __destruct()
    {
        if ($this->changed && count($this->config)) {
            $this->writeConfig($this->path, $this->config);
        }
    }
}
