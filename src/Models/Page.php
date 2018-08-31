<?php

namespace Netcore\Aven\Content\Models;

use Netcore\Aven\Content\Models\Translations\PageTranslation;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Czim\Paperclip\Model\PaperclipTrait;
use Netcore\Aven\Content\Registries\WidgetRegistry;
use Netcore\Aven\Content\Traits\PaperclipAndTranslatable;

class Page extends Model implements \Czim\Paperclip\Contracts\AttachableInterface
{
    use PaperclipTrait, PaperclipAndTranslatable;

    use Translatable {
        PaperclipAndTranslatable::getAttribute insteadof Translatable;
        PaperclipAndTranslatable::setAttribute insteadof Translatable;
    }

    use PaperclipTrait {
        PaperclipAndTranslatable::getAttribute insteadof PaperclipTrait;
        PaperclipAndTranslatable::setAttribute insteadof PaperclipTrait;
    }

    /**
     * @var array
     */
    protected $fillable = [
        'is_active',
        'is_homepage',
        'meta_image',
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

        'page'
    ];

    public function __construct()
    {
        $this->hasAttachedFile('meta_image', []);
    }

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
