<url>
    @if (! empty($tag->url))
    <loc>{{ url($tag->url) }}</loc>
    @endif
    @if (count($tag->alternates))
    @foreach ($tag->alternates as $alternate)
    <xhtml:link rel="alternate" hreflang="{{ $alternate->locale }}" href="{{ url($alternate->url) }}" />
    @endforeach
    @endif
    @if (! empty($tag->lastModificationDate))
    <lastmod>{{ $tag->lastModificationDate->format(DateTime::ATOM) }}</lastmod>
    @endif
    @if (! empty($tag->changeFrequency))
    <changefreq>{{ $tag->changeFrequency }}</changefreq>
    @endif
    @if (! empty($tag->priority))
    <priority>{{ number_format($tag->priority,1) }}</priority>
    @endif
    @if (count($tag->images))
    @foreach ($tag->images as $image)
    @if (! empty($image->url))
    <image:image>
        <image:loc>{{ url($image->url) }}</image:loc>
        @if (! empty($image->caption))
        <image:caption>{{ $image->caption }}</image:caption>
        @endif
        @if (! empty($image->geo_location))
        <image:geo_location>{{ $image->geo_location }}</image:geo_location>
        @endif
        @if (! empty($image->title))
        <image:title>{{ $image->title }}</image:title>
        @endif
        @if (! empty($image->license))
        <image:license>{{ $image->license }}</image:license>
        @endif
    </image:image>
    @endif
    @endforeach
    @endif
    @if (count($tag->news))
    @foreach ($tag->news as $new)
    <news:news>
        @if (! empty($new->publication_date))
        <news:publication_date>{{ $new->publication_date->format('Y-m-d') }}</news:publication_date>
        @endif
        @if (! empty($new->title))
        <news:title>{{ $new->title }}</news:title>
        @endif
        @if (! empty($new->name) || ! empty($new->language))
        <news:publication>
            @if (! empty($new->name))
            <news:name>{{ $new->name }}</news:name>
            @endif
            @if (! empty($new->language))
            <news:language>{{ $new->language }}</news:language>
            @endif
        </news:publication>
        @endif
    </news:news>
    @endforeach
    @endif
</url>