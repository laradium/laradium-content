<?php

namespace Laradium\Laradium\Content\Registries;

use Illuminate\Support\Collection;

class WidgetRegistry {

    protected $widgets;

    /**
     * WidgetRegistry constructor.
     */
    public function __construct()
    {
        $this->widgets = new Collection;
    }

    /**
     * @param $widgetClass
     * @return $this
     */
    public function register($widgetClass)
    {
        $widget = new $widgetClass;
        $model = $widget->model();
        $this->widgets->put($widgetClass, $model);

        return $this;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        return $this->widgets;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getByModel($value)
    {
        return $this->all()->filter(function ($model, $widget) use($value) {
            return $model === $value;
        })->keys()->first();
    }
}