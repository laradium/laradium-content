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
     * @param null $locale
     * @param null $slug
     * @return mixed
     */
    public function resolve($locale = null, $slug = null)
    {
        $prependLocale = config('laradium-content.resolver.prepend_locale', false);

        if (!$prependLocale && !$slug) {
            $slug = $locale;
        } elseif ($locale) {
            app()->setLocale($locale);
        }

        $page = null;
        if (!$slug) {
            $page = Page::with(['blocks.block', 'content'])
                ->whereIsHomepage(true)
                ->whereIsActive(true)
                ->first();

            abort_if(!$page, 404);

            if ($page && config('laradium-content.use_homepage_slug', false) && trim($page->slug,
                    '/') !== $slug) {
                return redirect()->to($page->slug);
            }
        }

        if (!$page) {
            $locale = app()->getLocale();
            $page = Page::with(['blocks.block', 'content'])
                ->whereIsActive(true)
                ->whereHas('translations', function ($q) use ($slug, $locale) {
                    $q->whereSlug($slug)->whereLocale($locale);
                })->first();
        }

        $parentSlugs = $this->getParentSlugs($slug);
        $hasPageAndParent = $page && $page->parent;

        if ($hasPageAndParent && $page->parent_slugs !== $parentSlugs) {
            $page = null;
        }

        if (!$page) {
            $page = Page::with(['blocks.block', 'content'])
                ->whereIsActive(true)
                ->whereHas('translations', function ($q) use ($slug, $locale) {
                    $q->whereSlug(array_last(explode('/', $slug)))
                        ->whereLocale($locale);
                })->first();

            abort_if(!$page, 404);

            $hasSameParent = $page->parent_slugs === $parentSlugs;

            abort_if(!$page->parent || !$hasSameParent, 404);
        }

        $layout = $page->layout ?: array_first(array_keys(config('laradium-content.layouts', [
            'layouts.main' => 'Main'
        ])));

        return view('laradium-content::page', compact('page', 'layout'));
    }

    /**
     * @param string $slug
     * @return string
     */
    private function getParentSlugs($slug): ?string
    {
        $slugParts = explode('/', $slug);
        if (count($slugParts) <= 1) {
            return $slug;
        }

        unset($slugParts[count($slugParts) - 1]);

        return implode($slugParts, '/');
    }
}
