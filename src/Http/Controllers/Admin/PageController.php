<?php

namespace Laradium\Laradium\Content\Http\Controllers\Admin;

use Laradium\Laradium\Content\Models\ContentBlock;
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

            if ($page && config('laradium-content.use_homepage_slug', false) && trim($page->slug,
                    '/') !== $slug) {
                return redirect()->to($page->slug);
            }
        } else {
            $locale = app()->getLocale();
            $page = Page::with(['blocks.widget', 'content'])->whereHas('translations',
                function ($q) use ($slug, $locale) {
                    $q->whereSlug($slug)->whereLocale($locale);
                })->first();
        }

        if (!$page) {
            abort(404);
        }

        $layout = $page->layout ?: array_first(array_keys(config('laradium-content.layouts',
            ['layouts.main' => 'Main'])));

        return view('laradium-content::page', compact('page', 'layout'));
    }
}
