<?php

if (!function_exists('content')) {
    /**
     * @return mixed
     */
    function content()
    {
        return app(\Netcore\Aven\Content\Repositories\ContentRepository::class);
    }
}
