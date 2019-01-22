<?php

namespace Laradium\Laradium\Content\Models\Translations;

use App\Models\Page;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PageTranslation extends Model
{
    use Sluggable;

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
        'meta_noindex',

        'locale'
    ];


    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * Unique slug for each language
     *
     * @param Builder $query
     * @param Model $model
     * @param         $attribute
     * @param         $config
     * @param         $slug
     */
    public function scopeWithUniqueSlugConstraints(Builder $query, Model $model, $attribute, $config, $slug): void
    {
        $query->join('pages', 'page_translations.page_id', '=', 'pages.id')->where('locale', $this->locale);
    }
}
