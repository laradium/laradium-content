<?php

namespace Laradium\Laradium\Content\Registries;

use Illuminate\Support\Collection;

class WidgetRegistry {

    protected $widgets;

    public function __construct()
    {
        $this->widgets = new Collection;
    }

    public function register($widgetClass)
    {
        $widget = new $widgetClass;
        $model = $widget->model();
        $this->widgets->push([$widgetClass => $model]);

        return $this;
    }

    public function all()
    {
        return $this->widgets;
    }

    public function getByModel($value)
    {
        return $this->all()->filter(function ($item) use($value) {
            $item = array_flip($item);
            return isset($item[$value]);
        })->map(function ($item) {
            return key($item);
        })->first();
    }
}