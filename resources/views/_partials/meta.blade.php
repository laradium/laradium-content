<meta name="keywords"
      content="{{ isset($page) && $page->meta_keywords ? $page->meta_keywords : setting()->get('seo.meta_keywords') }}"/>
<meta name="description"
      content="{{ isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description') }}"/>

<!-- Social: Facebook / Open Graph -->
<meta property="og:title" content="{{ isset($page) ? $page->title : setting()->get('seo.meta_title') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image') }}">
<meta property="og:description" content="{{ isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description') }}">
<meta property="og:site_name" content="{{ config('app.name') }}">

<!-- Social: Twitter -->
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ isset($page) ? $page->title : setting()->get('seo.meta_title') }}">
<meta name="twitter:description" content="{{ isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description') }}">
<meta name="twitter:image:src" content="{{ isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image') }}">

<!-- Social: Google+ / Schema.org  -->
<meta itemprop="name" content="{{ isset($page) ? $page->title : setting()->get('seo.meta_title') }}">
<meta itemprop="description" content="{{ isset($page) && $page->meta_description? $page->meta_description : setting()->get('seo.meta_description') }}">
<meta itemprop="image" content="{{ isset($page) && $page->meta_image->exists() ? $page->meta_image->url() : setting()->get('seo.meta_image') }}">
