<?php

namespace Laradium\Laradium\Content\Models;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Content\Models\Translations\PageTranslation;
use Laradium\Laradium\Content\Registries\WidgetRegistry;
use Laradium\Laradium\Traits\PaperclipAndTranslatable;

class Page extends Model implements AttachableInterface
{

    /**
     * @string
     */
    public const CACHE_KEY = 'laradium::pages';

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
        'parent_id',
        'is_homepage',
        'meta_image',
        'layout',
        'key'
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

    /**
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Page constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('meta_image', []);

        parent::__construct($attributes);
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function widgets(): array
    {
        $widgetRegistry = app(WidgetRegistry::class);
        $widgets = $widgetRegistry->all()->mapWithKeys(function ($model, $widget) {
            return [$model => $widget];
        })->toArray();
        $blocks = $this->blocks;

        return cache()->rememberForever($this->getCacheKey(), function () use ($widgets, $blocks) {
            $widgetList = [];

            foreach ($blocks->load('block')->sortBy('sequence_no') as $block) {
                $widget = $widgets[$block->block_type];
                if ($widget) {
                    $widget = new $widget;
                    $widgetList[] = [
                        'view' => $widget->view(),
                        'block' => $block->block
                    ];
                }
            }

            return $widgetList;
        });
    }

    /**
     * @return string
     */
    public function getParentSlugsAttribute(): string
    {
        $parent = $this->parent;
        if ($parent) {
            return implode('/', collect($this->getSlug($parent, []))->reverse()->toArray());
        }

        return '';
    }

    /**
     * @param $parent
     * @param array $slugs
     * @return array
     */
    private function getSlug($parent, $slugs = []): array
    {
        if ($parent) {
            $slugs[] = $parent->slug;

            return $this->getSlug($parent->parent, $slugs);
        }

        return $slugs;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return self::CACHE_KEY . '_' . $this->id;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }
}