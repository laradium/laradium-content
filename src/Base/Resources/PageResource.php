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

        return laradium()->resource(function (FieldSet $set) use ($channelInstance, $pages, $model) {
            $set->block(9)->fields(function (FieldSet $set) use ($channelInstance, $model) {
                $set->tab('Main')->fields(function (FieldSet $set) use ($channelInstance, $model) {
                    $set->text('title')->rules('required|max:255')->translatable()->col(6);
                    $set->text('slug')
                        ->rules('max:255')
                        ->translatable()
                        ->col(6)
                        ->label($this->getSlugLabel($model));

                    $channelInstance->fields($set);
                });

                $set->tab('Meta')->fields(function (FieldSet $set) {
                    $set->text('meta_keywords')->translatable()->col(6);
                    $set->text('meta_title')->translatable()->col(6);
                    $set->textarea('meta_description')->translatable();
                    $set->file('meta_image')->rules('max:' . config('laradium.file_size', 2024));
                });
            });

            $set->block(3)->fields(function (FieldSet $set) use ($pages) {
                $set->select('layout')->options(config('laradium-content.layouts', ['layouts.main' => 'Main']));
                $set->select('parent_id')->options($pages)->label('Parent');
                $set->boolean('is_active')->col(6);
                $set->boolean('is_homepage')->col(6);
            });
        });
    }

    /**
     * @param $model
     * @return string
     */
    private function getSlugLabel($model): string
    {
        $slug = 'Slug';
        if (!$model->exists) {
            return $slug;
        }

        if ($model->parent) {
            return $slug . ' (<small><b>pre slug</b> ' . $model->parent_slugs . '</small>)';
        }

        return $slug;
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