<?php

if (!function_exists('content')) {
    /**
     * @return mixed
     */
    function content()
    {
        return app(\Laradium\Laradium\Content\Repositories\ContentRepository::class);
    }
}
