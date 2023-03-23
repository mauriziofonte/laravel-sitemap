<?php

namespace Mfonte\Sitemap;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Contracts\Sitemapable;
use Mfonte\Sitemap\Renderer\NativeRenderer;
use Mfonte\Sitemap\Tags\Tag;
use Mfonte\Sitemap\Tags\Url;

class Sitemap implements Responsable, Renderable
{
    /** @var \Mfonte\Sitemap\Tags\Url[] */
    protected $tags = [];

    public static function create()
    {
        return new static();
    }

    final public function __construct()
    {
    }

    /**
     * @param $tag string|Url|Sitemapable
     */
    public function add($tag)
    {
        if (is_object($tag) && array_key_exists(Sitemapable::class, class_implements($tag))) {
            $tag = $tag->toSitemapTag();
        }

        if (is_iterable($tag)) {
            foreach ($tag as $item) {
                $this->add($item);
            }

            return $this;
        }

        if (is_string($tag)) {
            $tag = Url::create($tag);
        }

        if (! in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getUrl(string $url): ?Url
    {
        return collect($this->tags)->first(function (Tag $tag) use ($url) {
            return $tag->getType() === 'url' && $tag->url === $url;
        });
    }

    public function hasUrl(string $url): bool
    {
        return (bool) $this->getUrl($url);
    }

    public function hasImages() : bool
    {
        return (bool) collect($this->tags)->first(function (Tag $tag) {
            return $tag->getType() === 'url' && ! empty($tag->images);
        });
    }

    public function hasNews() : bool
    {
        return (bool) collect($this->tags)->first(function (Tag $tag) {
            return $tag->getType() === 'url' && ! empty($tag->news);
        });
    }

    /**
     * Renders the sitemap.
     * Optionally, you can pass a boolean to use the native renderer instead of the blade template. This is useful if you need to use this package in a non-Laravel project.
     *
     * @param bool $nativeRenderer - if true, uses the native renderer instead of the blade template (default: false)
     *
     * @return string
     */
    public function render(bool $nativeRenderer = false): string
    {
        $tags = collect($this->tags)->unique('url')->filter();
        $hasImages = $this->hasImages();
        $hasNews = $this->hasNews();

        if (! $nativeRenderer) {
            return view('sitemap::sitemap')
                ->with(compact('tags', 'hasImages', 'hasNews'))
                ->render();
        } else {
            $renderer = NativeRenderer::instance(compact('tags', 'hasImages', 'hasNews'));

            return $renderer->render('sitemap');
        }
    }

    /**
     * Writes the sitemap to file.
     * Optionally, you can pass a boolean to use the native renderer instead of the blade template. This is useful if you need to use this package in a non-Laravel project.
     *
     * @param string $path
     * @param bool $nativeRenderer - if true, uses the native renderer instead of the blade template (default: false)
     * @param int $flags - see https://www.php.net/manual/en/function.file-put-contents.php
     * @param resource $context - see https://www.php.net/manual/en/function.file-put-contents.php
     *
     * @return self
     */
    public function writeToFile(string $path, bool $nativeRenderer = false, int $flags = 0, $context = null) : self
    {
        file_put_contents($path, $this->render($nativeRenderer), $flags, $context);

        return $this;
    }

    public function writeToDisk(string $disk, string $path) : self
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
