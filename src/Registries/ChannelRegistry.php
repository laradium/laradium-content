<?php

namespace Laradium\Laradium\Content\Registries;

use Illuminate\Support\Collection;

class ChannelRegistry
{

    /**
     * @var string
     */
    protected $routeSlug;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var Collection
     */
    protected $channels;

    /**
     * RouteRegistry constructor.
     */
    public function __construct()
    {
        $this->channels = new Collection;

    }

    /**
     * @param $channel
     * @return $this
     */
    public function register($channel)
    {
        $name = $this->getName($channel);
        $this->channels->push([
            $name => $channel
        ]);

        return $this;
    }
    /**
     * @param $channel
     * @return string
     */
    protected function getName($channel): string
    {
        $explode = explode('\\',
            $channel); // we use explode because we want to remove namespace from controller path
        $resourceName = array_pop($explode); // get the name of the controller
        $name = str_replace('Channel', '', $resourceName); // remove "Resource" from name
        $name = $pieces = preg_split('/(?=[A-Z])/', $name);
        unset($name[0]); // unset empty element
        $name = str_plural(strtolower(implode('-', $name)));

        return $name;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->channels;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getChannelByName($name)
    {
        return $this->channels->filter(function ($item) use ($name) {
            return $item[$name] ?? false;
        })->map(function ($item) {
            return array_first($item);
        })->first();
    }
}