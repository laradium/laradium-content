<?php

namespace Laradium\Laradium\Content\Base\Resources;

use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Content\Models\Page;
use \Laradium\Laradium\Content\Registries\ChannelRegistry;

Class PageResource extends AbstractResource
{

    /**
     * @var string
     */
    protected $resource = Page::class;

    /**
     * @var array
     */
    protected $actions = [
        'edit',
        'delete'
    ];

    /**
     * @var ChannelRegistry
     */
    private $channelRegistry;

    public function __construct()
    {
        parent::__construct();

        $this->channelRegistry = app(ChannelRegistry::class);
    }

    /**
     * @return \Laradium\Laradium\Base\Resource
     */
    public function resource()
    {
        $model = $this->getModel();
        $channelInstance = $this->getChannelInstance($model);
        $pages = $this->getPages();

        $this->event('beforeSave', function ($model, $request) {
            $this->handleParentSlugBeforeSave($model, $request);
        });

        $this->event('afterSave', function ($model, $request) {
            $this->handleParentSlugAfterSave($model, $request);
        });

        return laradium()->resource(function (FieldSet $set) use ($channelInstance, $pages) {
            $set->tab('Main')->fields(function (FieldSet $set) use ($channelInstance, $pages) {
                $set->text('title')->rules('required|max:255')->translatable()->col(6);
                $set->text('slug')->rules('max:255')->translatable()->col(6);
                $set->select('layout')->options(config('laradium-content.layouts', ['layouts.main' => 'Main']))->col(6);
                $set->select('parent_id')->options($pages)->label('Parent')->col(6);
                $set->boolean('is_active')->col(6);
                $set->boolean('is_homepage')->col(6);

                $channelInstance->fields($set);
            });
            $set->tab('Meta')->fields(function (FieldSet $set) {
                $set->text('meta_keywords')->translatable()->col(6);
                $set->text('meta_title')->translatable()->col(6);
                $set->textarea('meta_description')->translatable();
                $set->file('meta_image')->rules('max:' . config('laradium.file_size', 2024));
            });
        });
    }

    /**
     * @return \Laradium\Laradium\Base\Table
     */
    public function table()
    {
        return laradium()->table(function (ColumnSet $column) {
            $column->add('title')->translatable();
            $column->add('slug')->translatable();
            $column->add('is_active', 'Is Visible?')->modify(function ($item) {
                return $item->is_active ? 'Yes' : 'No';
            });
            $column->add('content_type', 'Type')->modify(function ($item) {
                if ($item->content_type) {
                    return array_last(explode('\\', $item->content_type));
                }

                return 'Main';
            });
        })
            ->relations(['translations'])
            ->additionalView('laradium-content::admin.pages.index-top', [
                'channels' => $this->channelRegistry->all()
            ]);
    }

    /**
     * @return array
     */
    private function getPages(): array
    {
        $pages = [null => '-- Select --'];
        $pageLsit = Page::where('id', '!=', $this->getModel()->id)->get()->mapWithKeys(function ($page) {
            return [(string)$page->id => $page->title];
        })->toArray();
        $pages += $pageLsit;

        return $pages;
    }

    /**
     * @param $model
     * @param $request
     * @return void
     */
    private function handleParentSlugAfterSave($model, $request): void
    {
        $parentId = $request->get('parent_id');
        if (!$parentId) {
            return;
        }
        $parent = Page::find($request->get('parent_id'));
        foreach ($request->get('translations') as $locale => $translation) {
            $slug = array_get($translation, 'slug');
            $modelTranslations = $model->translations()->where('locale', $locale);
            if (!$slug) {
                $slug = $modelTranslations->first()->slug;
            }
            $parentTranslation = $parent->translations->where('locale', $locale)->first();
            if ($parentTranslation && !str_contains($slug, $parentTranslation->slug)) {
                $slug ?? str_slug(array_get($translation, 'title'));
                $newSlug = sprintf('%s/%s', $parentTranslation->slug, $slug);
                $model->translations()->where('locale', $locale)->update(['slug' => $newSlug]);
            }
        }
    }

    /**
     * @param $model
     * @param $request
     * @return void
     */
    private function handleParentSlugBeforeSave($model, $request): void
    {
        $parentId = $request->get('parent_id');
        if (!$model->parent_id || $parentId == $model->parent_id) {
            return;
        }
        $model = Page::find($model->id);
        $parent = Page::find($model->parent_id);
        if (!$parent) {
            return;
        }
        foreach ($model->translations()->get() as $translation) {
            $slug = $translation->slug;
            $locale = $translation->locale;

            $parentTranslation = $parent->translations->where('locale', $locale)->first();
            if (!$parentTranslation) {
                continue;
            }
            if ($parentTranslation && str_contains($slug, $parentTranslation->slug)) {
                $newSlug = str_replace($parentTranslation->slug . '/', '', $slug);
                $translation->slug = $newSlug;
                $translation->save();
                $requestTranslations = $request->get('translations');
                if ($translation = array_get($requestTranslations, $locale . '.slug')) {
                    $requestTranslations[$locale]['slug'] = str_replace($parentTranslation->slug . '/', '',
                        $translation);
                    $request->merge([
                        'translations' => $requestTranslations
                    ]);
                }

            }
        }
    }

    /**
     * @param $model
     */
    private function getChannelInstance($model)
    {
        $channelName = request()->get('channel', 'main');
        $channelModel = null;
        if ($model->exists && $model->content_type) {
            $channelModel = $model->content_type;
        }
        $channel = $this->getChannel($channelModel, $channelName);

        return new $channel['class'];
    }

    /**
     * @param $channelModel
     * @param $channelName
     * @return array
     */
    private function getChannel($channelModel, $channelName): array
    {
        $channelRegistry = $this->channelRegistry->all();
        $channel = $channelRegistry->where('model', $channelModel)->first();

        if ($channelModel && $channel) {
            return $channel;
        }

        return $channelRegistry->where('name', $channelName)->first();
    }
}