@php
    $metaTags = [
        [
            'attribute' => 'name',
            'name' => 'keywords',
            'value' => isset($page) && $page->meta_keywords ? $page->meta_keywords : setting()->get('seo.meta_keywords')
        ],
        [
            'attribute' => 'name',
            'name' => 'description',
            'value' => isset($page) && $page->meta_description ? $page->meta_description : setting()->get('seo.meta_description')
        ],

        // Social: Facebook / Open Graph
        [
            'attribute' => 'property',
            'name' => 'og:title',
            'value' => isset($page) ? $page->title : setting()->get('seo.meta_title')
        ],
        [
            'attribute' => 'property',
            'name' => 'og:type',
            'value' => 'website'
        ],
        [
            'attribute' => 'property',
            'name' => 'og:url',
            'value' => url()->current()
        ],
        [
            'attribute' => 'property',
            'name' => 'og:image',
            'value' => isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image')
        ],
        [
            'attribute' => 'property',
            'name' => 'og:description',
            'value' => isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description')
        ],
        [
            'attribute' => 'property',
            'name' => 'og:site_name',
            'value' => config('app.name')
        ],

        // Social: Twitter
        [
            'attribute' => 'name',
            'name' => 'twitter:card',
            'value' => 'summary'
        ],
        [
            'attribute' => 'name',
            'name' => 'twitter:site',
            'value' => url()->current()
        ],
        [
            'attribute' => 'name',
            'name' => 'twitter:title',
            'value' => isset($page) ? $page->title : setting()->get('seo.meta_title')
        ],
        [
            'attribute' => 'name',
            'name' => 'twitter:description',
            'value' => isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description')
        ],
        [
            'attribute' => 'name',
            'name' => 'twitter:image:src',
            'value' => isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image')
        ],

        // Social: Google+ / Schema.org
        [
            'attribute' => 'itemprop',
            'name' => 'name',
            'value' => isset($page) ? $page->title : setting()->get('seo.meta_title')
        ],
        [
            'attribute' => 'itemprop',
            'name' => 'description',
            'value' => isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description')
        ],
        [
            'attribute' => 'itemprop',
            'name' => 'image',
            'value' => isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image')
        ],
    ]
@endphp

@foreach($metaTags as $metaTag)
    @if($metaTag['value'])
        <meta {{ $metaTag['attribute'] }}="{{ $metaTag['name'] }}" content="{{ $metaTag['value'] }}">
    @endif
@endforeach
