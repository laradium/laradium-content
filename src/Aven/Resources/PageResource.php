<?php

namespace Netcore\Aven\Content\Aven\Resources;

use Netcore\Aven\Content\Models\Page;
use Illuminate\Http\Request;
use Netcore\Aven\Aven\AbstractAvenResource;
use Netcore\Aven\Aven\FieldSet;
use Netcore\Aven\Aven\Form;
use Netcore\Aven\Aven\Resource;
use Netcore\Aven\Aven\ColumnSet;
use Netcore\Aven\Aven\Table;

Class PageResource extends AbstractAvenResource
{

    /**
     * @var string
     */
    protected $resource = Page::class;

    /**
     * @return \Netcore\Aven\Aven\Resource
     */
    public function resource()
    {
        return (new Resource)->make(function (FieldSet $set) {

            $channelName = session()->get('channel');
            $channelRegistry = app(\Netcore\Aven\Content\Registries\ChannelRegistry::class);
            $channel = $channelRegistry->getChannelByName($channelName);
            $channelInstance = new $channel;

            $set->text('title')->translatable();
            $set->text('slug')->translatable();

            $set->boolean('is_active');
            $set->boolean('is_homepage');

            $set->tab('Meta')->fields(function (FieldSet $set) {
                $set->text('meta_keywords')->translatable();
                $set->text('meta_title')->translatable();
                $set->text('meta_description')->translatable();
                $set->text('meta_url')->translatable();
                $set->text('meta_image')->translatable();
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
        $page = Page::find($id);
        if ($page->content_type) {
            $channelName = str_plural(array_last(explode('\\', $page->content_type)));
            session()->put('channel', strtolower($channelName));
        } else {
            session()->put('channel', 'mains');
        }

        return parent::edit($id);
    }

    /**
     * @return Table
     */
    public function table()
    {
        $channelRegistry = app(\Netcore\Aven\Content\Registries\ChannelRegistry::class);
        $channels = $channelRegistry->all()->mapWithKeys(function ($item) {
            return [str_singular(key($item)) => ucfirst(str_replace('-', ' ', str_singular(key($item))))];
        });

        return (new Table)->make(function (ColumnSet $column) {
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
            ->additionalView('aven-content::admin.pages.index-top', compact('channels'));
    }
}