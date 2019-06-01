@php
    $prependLocale = config('laradium-content.resolver.prepend_locale', false);
@endphp

@foreach(translate()->languages() as $language)
    @php
        $translation = $item->translations->where('locale', $language->iso_code)->first();
    @endphp
    @if($translation->{$column})
        <li>
            @php
                $preSlug = '';
                if(get_class($item) === \Laradium\Laradium\Content\Models\Page::class && $item->parent) {
                    $preSlug = $item->getParentSlugsByLocale($item->parent, $language->iso_code) . '/';
                }
                $uri = $prependLocale ? $language->iso_code.'/'.$preSlug.$translation->{$column} : $preSlug.$translation->{$column};
            @endphp
            <b>{{ strtoupper($language->iso_code) }}: </b>
            <a href="{{ config('laradium-content.page_preview_url', url('/')) . '/' . $uri }}?preview=true"
               target="_blank">
                {{ $preSlug.$translation->{$column} }}
            </a>
        </li>
    @else
        <li><b>{{ strtoupper($language->iso_code) }}: </b>Not set</li>
    @endif
@endforeach