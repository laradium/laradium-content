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
     * @param $channelClass
     * @return $this
     */
    public function register($channelClass)
    {
        $channel = new $channelClass;
        $model = object_get($channel, 'model', null);
        $name = $this->getName($channelClass);
        $this->channels->push([
            'class' => $channelClass,
            'model' => $model,
            'name'  => $name,
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
        $name = strtolower(implode('-', $name));

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
        return $this->channels->filter(function ($class, $id) use ($name) {
            return $id === $name;
        })->first();
    }
}