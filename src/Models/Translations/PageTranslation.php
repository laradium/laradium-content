<?php

namespace Netcore\Aven\Content\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',

        // meta
        'meta_keywords',
        'meta_title',
        'meta_description',
        'meta_url',
        'meta_image',

        'locale'
    ];
}
