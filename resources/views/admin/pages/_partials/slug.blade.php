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
            @endphp
            <b>{{ strtoupper($language->iso_code) }}: </b>
            <a href="{{ url($prependLocale ? $language->iso_code.'/'.$preSlug.$translation->{$column} : $preSlug.$translation->{$column}) }}"
               target="_blank">
                {{ $preSlug.$translation->{$column} }}
            </a>
        </li>
    @else
        <li><b>{{ strtoupper($language->iso_code) }}: </b>Not set</li>
    @endif
@endforeach