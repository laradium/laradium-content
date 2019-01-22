<?php

namespace Laradium\Laradium\Content\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Laradium\Laradium\Content\Models\Page;
use SimpleXMLElement;

class SitemapController
{
    /**
     * Display sitemap with existing pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $pages = $this->fetchPages()->merge($this->fetchCustomPages());

        foreach ($pages as $page) {
            if (!$page->url) {
                continue;
            }

            $item = $xml->addChild('url');
            $item->addChild('lastmod', $page->updated_at);
            $item->addChild('loc', $page->url);
        }

        return response($xml->saveXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Get all existing and active pages.
     *
     * @return \Illuminate\Support\Collection
     */
    private function fetchPages(): Collection
    {
        $prependLocale = config('laradium-content.resolver.prepend_locale', false);

        $pages = Page::with('translations')->where('is_active', 1)->get();

        $urls = collect();

        $pages->each(function ($page) use ($urls, $prependLocale) {
            $page->translations->each(function ($translation) use ($page, $urls, $prependLocale) {
                if ($translation->meta_noindex) {
                    return;
                }

                $preSlug = '';
                if ($page->parent && get_class($page) === Page::class) {
                    $preSlug = $page->getParentSlugsByLocale($page->parent, $translation->locale) . '/';
                }

                if ($translation->slug) {
                    $urls->push((object)[
                        'url'        => url($prependLocale ? $translation->locale . '/' . $preSlug . $translation->slug : $preSlug . $translation->slug),
                        'updated_at' => $page->updated_at->format('Y-m-d'),
                    ]);
                }
            });
        });

        $urls->prepend((object)[
            'url'        => url('/'),
            'updated_at' => date('Y-m-d', strtotime('today')),
        ]);

        return $urls;
    }

    /**
     * Returns custom urls defined in config
     *
     * @return Collection
     */
    private function fetchCustomPages(): Collection
    {
        $customSitemap = config('laradium-content.sitemap', []);
        if (!$customSitemap) {
            return collect();
        }

        if (is_string($customSitemap) && function_exists($customSitemap)) {
            $customSitemap = $customSitemap();
        }

        $urls = collect();

        foreach ($customSitemap as $page) {
            if (is_string($page)) {
                $urls->push((object)[
                    'url'        => url($page),
                    'updated_at' => date('Y-m-d')
                ]);

                continue;
            }

            if (!isset($page['uri'])) {
                continue;
            }

            if (!isset($page['updated_at'])) {
                $page['updated_at'] = date('Y-m-d');
            }

            $urls->push((object)[
                'url'        => url($page['uri']),
                'updated_at' => $page['updated_at']
            ]);
        }

        return $urls;
    }
}