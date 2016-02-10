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
        $this->config = $this->readConfig();
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

    public function get($property, $default = null)
    {
        $value = $this->accessor->getValue($this->config, $this->toArrayProperty($property));

        if (null === $value) {
            return $default;
        }

        return $value;
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

    public function getGroups()
    {
        return array_keys($this->getGroupsConfig());
    }

    public function hasGroup($group)
    {
        return isset($this->config[$group]);
    }

    public function hasConfigFile()
    {
        return file_exists($this->path);
    }

    private function readConfig()
    {
        if ($this->hasConfigFile()) {
            return Yaml::parse(file_get_contents($this->path));
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
