<?php

namespace Netcore\Aven\Content\Aven;

use Netcore\Aven\Aven\FieldSet;

abstract class AbstractWidget
{

    /**
     * @return mixed
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return $this->view;
    }

    /**
     * @param  $widget
     * @return string
     * @throws \Throwable
     */
    public function render($widget): string
    {
        return view($this->view(), compact('widget'))->render();
    }

    /**
     * @param FieldSet $set
     * @return mixed
     */
    abstract function fields(FieldSet $set);

}