<?php

namespace Netcore\Aven\Content\Models;

use Netcore\Aven\Content\Models\Translations\PageTranslation;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Netcore\Aven\Content\Registries\WidgetRegistry;

class Page extends Model
{

    use Translatable;

    /**
     * @var array
     */
    protected $fillable = [
        'is_active',
        'is_homepage',
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

    /**
     * @return array
     */
    public function widgets()
    {
        $widgetRegistry = app(WidgetRegistry::class);
        $widgets = $widgetRegistry->all()->mapWithKeys(function ($widget) {
            return array_flip($widget);
        })->toArray();

        $widgetList = [];
        foreach ($this->blocks as $block) {
            $widget = $widgets[$block->block_type];
            $widget = new $widget;
            $widgetList[] = view($widget->view(), [
                'widget' => $block->block
            ]);
        }

        return $widgetList;
    }
}
