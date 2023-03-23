<?php

namespace Mfonte\Sitemap;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Renderer\NativeRenderer;
use Mfonte\Sitemap\Tags\Sitemap;
use Mfonte\Sitemap\Tags\Tag;

class SitemapIndex implements Responsable, Renderable
{
    /** @var \Mfonte\Sitemap\Tags\Sitemap[] */
    protected array $tags = [];

    public static function create()
    {
        return new static();
    }

    final public function __construct()
    {
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

    /**
     * Renders the sitemap index.
     * Optionally, you can pass a boolean to use the native renderer instead of the blade template. This is useful if you need to use this package in a non-Laravel project.
     *
     * @param bool $nativeRenderer - if true, uses the native renderer instead of the blade template (default: false)
     *
     * @return string
     */
    public function render(bool $nativeRenderer = false): string
    {
        $tags = $this->tags;

        if (! $nativeRenderer) {
            return view('sitemap::sitemapIndex/index')
                ->with(compact('tags'))
                ->render();
        } else {
            $renderer = NativeRenderer::instance(compact('tags'));

            return $renderer->render('sitemapIndex');
        }
    }

    /**
     * Writes the sitemap index to file.
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
