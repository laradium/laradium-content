@php
    $prependLocale = config('laradium-content.resolver.prepend_locale', false);
@endphp

@foreach(translate()->languages() as $language)
    @php
        $translation = $item->translations->where('locale', $language->iso_code)->first();
    @endphp
    @if($translation->{$column})
        <li>
            <b>{{ strtoupper($language->iso_code) }}: </b>
            <a href="{{ url($prependLocale ? $language->iso_code.'/'.$translation->{$column} : $translation->{$column}) }}"
               target="_blank">
                {{ $translation->{$column} }}
            </a>
        </li>
    @else
        <li><b>{{ strtoupper($language->iso_code) }}: </b>Not set</li>
    @endif
@endforeach