<?php

namespace Netcore\Aven\Content\Models;

use Netcore\Aven\Content\Models\Translations\PageTranslation;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

    use Translatable;

    /**
     * @var array
     */
    protected $fillable = [
        'is_active'
    ];

    /**
     * @var string
     */
    protected $translationModel = PageTranslation::class;

    /**
     * @var array
     */
    protected $translatedAttributes = [
        'title',
        'slug',

        // meta
        'meta_keywords',
        'meta_title',
        'meta_description',
        'meta_url',
        'meta_image',

        'page'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blocks()
    {
        return $this->hasMany(ContentBlock::class);
    }
}
