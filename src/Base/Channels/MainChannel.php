<?php

namespace Laradium\Laradium\Content\Laradium\Channels;

use Laradium\Laradium\Base\FieldSet;

Class MainChannel
{

    /**
     * @var string
     */
    public $layout = 'layouts.main';

    /**
     * @param FieldSet $set
     */
    public function fields(FieldSet $set)
    {
        $set->widgetConstructor();
    }
}