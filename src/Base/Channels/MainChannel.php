<?php

namespace Laradium\Laradium\Content\Base\Channels;

use Laradium\Laradium\Base\FieldSet;

class MainChannel
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