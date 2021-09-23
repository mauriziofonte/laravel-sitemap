<?= '<'.'?'.'xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" @if ($hasImages)xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"@endif @if ($hasNews)xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"@endif>
@foreach($tags as $tag)
    @include('sitemap::' . $tag->getType())
@endforeach
</urlset>
