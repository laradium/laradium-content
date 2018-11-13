<?php

namespace Laradium\Laradium\Content\Base\Resources;

use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Content\Models\Page;

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
     * @return \Laradium\Laradium\Base\Resource
     */
    public function resource()
    {
        $model = $this->getModel();
        $channelName = request()->get('channel', 'main');
        $channelModel = null;
        if ($model->exists && $model->content_type) {
            $channelModel = $model->content_type;
        }
        $channelRegistry = app(\Laradium\Laradium\Content\Registries\ChannelRegistry::class);
        $channels = $channelRegistry->all();
        $channel = null;
        if ($channelModel && $channel = $channels->where('model', $channelModel)->first()) {
        } else {
            $channel = $channels->where('name', $channelName)->first();
        }
        $channelInstance = new $channel['class'];

        return laradium()->resource(function (FieldSet $set) use ($channelInstance) {
            $set->tab('Main', 'asd')->fields(function (FieldSet $set) use ($channelInstance) {
                $set->text('title')->rules('required|max:255')->translatable();
                $set->text('slug')->rules('max:255')->translatable();
                $set->select('layout')->options(config('laradium-content.layouts', ['layouts.main' => 'Main']));

                $set->boolean('is_active');
                $set->boolean('is_homepage');

                $channelInstance->fields($set);
            });
            $set->tab('Meta')->fields(function (FieldSet $set) {
                $set->text('meta_keywords')->translatable();
                $set->text('meta_title')->translatable();
                $set->text('meta_description')->translatable();
                $set->file('meta_image')->rules('max:' . config('laradium.file_size', 2024));
            });
        });
    }

    /**
     * @return \Laradium\Laradium\Base\Table
     */
    public function table()
    {
        $channelRegistry = app(\Laradium\Laradium\Content\Registries\ChannelRegistry::class);
        $channels = $channelRegistry->all();

        $table = laradium()->table(function (ColumnSet $column) {
            $column->add('id', '#ID');
            $column->add('is_active', 'Is Visible?')->modify(function ($item) {
                return $item->is_active ? 'Yes' : 'No';
            });
            $column->add('title')->translatable();
            $column->add('slug')->translatable();
            $column->add('content_type', 'Type')->modify(function ($item) {
                if ($item->content_type) {
                    return array_last(explode('\\', $item->content_type));
                }

                return 'Main';
            });
        })
            ->relations(['translations'])
            ->additionalView('laradium-content::admin.pages.index-top', compact('channels'));

        return $table;
    }
}