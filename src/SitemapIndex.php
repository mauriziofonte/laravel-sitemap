<?php

namespace Mfonte\Sitemap;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Tags\Sitemap;
use Mfonte\Sitemap\Tags\Tag;

class SitemapIndex implements Responsable, Renderable
{
    /** @var \Spatie\Sitemap\Tags\Sitemap[] */
    protected array $tags = [];

    public static function create()
    {
        return new static();
    }

    /**
     * @param $tag string|Sitemap
     */
    public function add($tag)
    {
        if (is_string($tag)) {
            $tag = Sitemap::create($tag);
        }

        $this->tags[] = $tag;

        return $this;
    }

    public function getSitemap(string $url): ?Sitemap
    {
        return collect($this->tags)->first(function (Tag $tag) use ($url) {
            return $tag->getType() === 'sitemap' && $tag->url === $url;
        });
    }

    public function hasSitemap(string $url): bool
    {
        return (bool) $this->getSitemap($url);
    }

    public function render(): string
    {
        $tags = $this->tags;

        return view('sitemap::sitemapIndex/index')
            ->with(compact('tags'))
            ->render();
    }

    public function writeToFile(string $path)
    {
        file_put_contents($path, $this->render());

        return $this;
    }

    public function writeToDisk(string $disk, string $path)
    {
        Storage::disk($disk)->put($path, $this->render());

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return Response::make($this->render(), 200, [
            'Content-Type' => 'text/xml',
        ]);
    }
}
