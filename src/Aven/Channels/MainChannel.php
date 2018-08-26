<?php

namespace Netcore\Aven\Content\Aven\Channels;

use Netcore\Aven\Aven\FieldSet;

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