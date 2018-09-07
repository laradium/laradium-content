<?php

namespace Laradium\Laradium\Content\Http\Controllers\Admin;

use Laradium\Laradium\Content\Base\Channels\MainChannel;
use Laradium\Laradium\Content\Models\ContentBlock;
use Illuminate\Http\Request;
use Laradium\Laradium\Content\Models\Page;

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
        } else {
            $page = Page::with(['blocks.widget', 'content'])->whereHas('translations', function($q) use($slug) {
                $q->whereSlug($slug);
            })->first();
        }

        if(!$page) {
            abort(404);
        }

        $layout = $page->layout ?: array_first(array_keys(config('laradium-content.layouts', ['layouts.main' => 'Main'])));

        if (!$page) {
            abort(404);
        }

        return view('laradium-content::page', compact('page', 'layout'));
    }
}
