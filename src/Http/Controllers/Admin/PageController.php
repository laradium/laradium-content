<?php

namespace Netcore\Aven\Content\Http\Controllers\Admin;

use Netcore\Aven\Content\Aven\Channels\MainChannel;
use Netcore\Aven\Content\Models\ContentBlock;
use Illuminate\Http\Request;
use Netcore\Aven\Content\Models\Page;
use Netcore\Aven\Content\Registries\WidgetRegistry;

class PageController
{


    /**
     * @param $id
     * @return array
     */
    public function contentBlockDelete($id)
    {
        $contentBlock = ContentBlock::find($id);

        $blockClass = $contentBlock->block_type;
        $blockId = $contentBlock->block_id;
        $block = new $blockClass;
        $block->find($blockId)->delete();

        $contentBlock->delete();

        return [
            'state' => 'success'
        ];
    }


    /**
     * @param null $slug
     * @return mixed
     */
    public function resolve($slug = null)
    {
        $page = null;
        if (!$slug) {
            $page = Page::with(['blocks.widget', 'content'])->whereIsHomepage(true)->first();
        }

        if(!$page) {
            abort(404);
        }

        $channel = $page->content_type ?: MainChannel::class;
        $channel = new $channel;
        $layout = $channel->layout;

        if (!$page) {
            abort(404);
        }

        return view('aven-content::page', compact('page', 'layout'));
    }
}
