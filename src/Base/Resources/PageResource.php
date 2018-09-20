<?php

namespace Laradium\Laradium\Content\Base\Resources;

use Laradium\Laradium\Content\Models\Page;
use Illuminate\Http\Request;
use Laradium\Laradium\Base\AbstractResource;
use Laradium\Laradium\Base\FieldSet;
use Laradium\Laradium\Base\Form;
use Laradium\Laradium\Base\Resource;
use Laradium\Laradium\Base\ColumnSet;
use Laradium\Laradium\Base\Table;

Class PageResource extends AbstractResource
{

    /**
     * @var string
     */
    protected $resource = Page::class;

    /**
     * @return \Laradium\Laradium\Base\Resource
     */
    public function resource()
    {
        return laradium()->resource(function (FieldSet $set) {

            $channelName = session()->get('channel');
            $channelRegistry = app(\Laradium\Laradium\Content\Registries\ChannelRegistry::class);
            $channel = $channelRegistry->getChannelByName($channelName);
            $channelInstance = new $channel;

            $set->text('title')->translatable();
            $set->text('slug')->translatable();
            $set->select('layout')->options(config('laradium-content.layouts', ['layouts.main' => 'Main']));

            $set->boolean('is_active');
            $set->boolean('is_homepage');

            $set->tab('Meta')->fields(function (FieldSet $set) {
                $set->text('meta_keywords')->translatable();
                $set->text('meta_title')->translatable();
                $set->text('meta_description')->translatable();
                $set->file('meta_image')->rules('max:' . config('laradium.file_size', 2024));
            });

            $channelInstance->fields($set);
        });
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $channelName = str_plural(array_last(explode('/', request()->getRequestUri())));
        session()->put('channel', $channelName);

        return parent::create();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);

        if ($page->content_type) {
            $channelName = str_plural(array_last(explode('\\', $page->content_type)));
            session()->put('channel', strtolower($channelName));
        } else {
            session()->put('channel', 'mains');
        }

        return parent::edit($id);
    }

    /**
     * @return \Laradium\Laradium\Base\Table
     */
    public function table()
    {
        $channelRegistry = app(\Laradium\Laradium\Content\Registries\ChannelRegistry::class);
        $channels = $channelRegistry->all()->mapWithKeys(function ($item) {
            return [str_singular(key($item)) => ucfirst(str_replace('-', ' ', str_singular(key($item))))];
        });

        return laradium()->table(function (ColumnSet $column) {
            $column->add('id', '#ID');
            $column->add('is_active', 'Is Visible?')->modify(function ($item) {
                return $item->is_active ? 'Yes' : 'No';
            });
            $column->add('title')->translatable();
            $column->add('content_type', 'Type')->modify(function ($item) {
                if ($item->content_type) {
                    return array_last(explode('\\', $item->content_type));
                } else {
                    return 'Main';
                }
            });
        })
            ->actions(['edit', 'delete'])
            ->relations(['translations'])
            ->additionalView('laradium-content::admin.pages.index-top', compact('channels'));
    }
}