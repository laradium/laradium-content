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
    public function view()
    {
        return $this->view;
    }

    /**
     * @param FieldSet $set
     * @return mixed
     */
    abstract function fields(FieldSet $set);

}