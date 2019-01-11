<?php

namespace Laradium\Laradium\Content\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Laradium\Laradium\Content\Models\Page;

class SitemapController
{
    /**
     * Display sitemap with existing pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

        $urls = $this->fetchUrls();

        foreach ($urls as $url) {
            $str .= "<url>";
            $str .= "<lastmod>" . $url->updated_at . "</lastmod>";
            $str .= "<loc>" . $url->url . "</loc>";
            $str .= "</url>";
        }

        $str .= "</urlset>";

        return response($str, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Get all existing page urls.
     *
     * @return \Illuminate\Support\Collection
     */
    private function fetchUrls(): Collection
    {
        $query = Page::getQuery();
        $query->leftJoin('page_translations as pt', 'pt.page_id', '=', 'pages.id');
        $query->selectRaw('pt.slug, ANY_VALUE(pt.updated_at) as updated_at');
        $query->groupBy('pt.slug');

        $pages = $query->get();

        return $pages->map(function ($page) {
            return (object)[
                'url'        => url($page->slug),
                'updated_at' => explode(' ', $page->updated_at)[0],
            ];
        });
    }
}