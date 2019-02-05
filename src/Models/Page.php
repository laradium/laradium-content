<?php

namespace Laradium\Laradium\Content\Models;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laradium\Laradium\Content\Models\Translations\PageTranslation;
use Laradium\Laradium\Content\Registries\WidgetRegistry;
use Laradium\Laradium\Traits\PaperclipAndTranslatable;

class Page extends Model implements AttachableInterface
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
        'meta_noindex',

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
     * --------------------
     * Scopes
     * --------------------
     */

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        $includeInActive = request()->get('preview') && auth()->check() && auth()->user()->is_admin;

        if (!$includeInActive) {
            return $query->where('is_active', true);
        }

        return $query;
    }

    /**
     * --------------------
     * Relationships
     * --------------------
     */

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
     */
    public function widgets(): array
    {
        $widgetRegistry = app(WidgetRegistry::class);
        $widgets = $widgetRegistry->all()->mapWithKeys(function ($model, $widget) {
            return [$model => $widget];
        })->toArray();
        $widgetList = [];

        foreach ($this->blocks->load('block')->sortBy('sequence_no') as $block) {
            $widget = $widgets[$block->block_type];
            if ($widget) {
                $widget = new $widget;
                $widgetList[] = [
                    'view'  => $widget->view(),
                    'block' => $block->block
                ];
            }
        }

        return $widgetList;
    }

    /**
     * @return string
     */
    public function getParentSlugsAttribute(): string
    {
        return $this->getSlug($this->parent);
    }

    /**
     * @param $parent
     * @param array $slugs
     * @return string
     */
    public function getSlug($parent, $slugs = []): string
    {
        if ($parent) {
            $slugs[] = $parent->slug;

            return $this->getSlug($parent->parent, $slugs);
        }

        if ($slugs) {
            return implode('/', collect($slugs)->reverse()->toArray());
        }

        return '';
    }

    /**
     * @param $parent
     * @param $locale
     * @param array $slugs
     * @return string
     */
    public function getParentSlugsByLocale($parent, $locale, $slugs = []): string
    {
        if ($parent) {
            $slugs[] = $parent->translate($locale)->slug;

            return $this->getSlug($parent->parent, $slugs);
        }

        if ($slugs) {
            return implode('/', collect($slugs)->reverse()->toArray());
        }

        return '';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getParentsAttribute()
    {
        $parents = collect([]);

        $parent = $this->parent;

        while (!is_null($parent)) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getBreadcrumbsAttribute()
    {
        $breadcrumbs = collect([]);

        foreach ($this->parents->reverse() as $parent) {
            $breadcrumbs->push((object)[
                'title' => $parent->title,
                'slug'  => implode('/', $breadcrumbs->pluck('slug')->toArray()) . '/' . $parent->slug
            ]);
        }

        return $breadcrumbs;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }
}