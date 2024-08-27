<?php

namespace Mfonte\Sitemap\Renderer;

use \DateTime;

use Mfonte\Sitemap\Tags\Sitemap;
use Mfonte\Sitemap\Tags\Url;

class NativeRenderer
{
    /**
     * Params Array. Should be injected into current instance with a compact() call, for example: compact('tags', 'hasImages', 'hasNews')
     *
     * @var array
     */
    private array $params;
    private string $tempFile;

    public static function instance(array $params) : self
    {
        return new self($params);
    }

    public function __construct(array $params)
    {
        $this->params = $params;
        $this->tempFile = tempnam(sys_get_temp_dir(), 'mfonte_sitemap_nativerenderer_' . sha1(uniqid()));
    }

    /**
     * Renders the sitemap or sitemap index
     *
     * @param string $type - sitemap or sitemapIndex
     *
     * @return string
     */
    public function render(string $type) : string
    {
        try {
            switch($type) {
                case 'sitemap':
                    $this->renderSitemap();

                    break;
                case 'sitemapIndex':
                    $this->renderSitemapIndex();

                    break;
                default:
                    throw new \Exception('Invalid Render Type', 999);
            }
        } catch(\Exception $e) {
            if ($e->getCode() === 999) {
                throw new \Exception('The render type must be "sitemap" or "sitemapIndex"');
            }

            throw new \Exception('Error while rendering the xml: ' . $e->getMessage());
        }
        
        // if the tidy extension is not available, return the xml as it was rendered natively.
        if (! function_exists('tidy_parse_file')) {
            return $this->asString();
        }

        // if the tidy extension is available, format the xml with tidy
        $tidyInstance = tidy_parse_file($this->tempFile, [
            'indent'         => true,
            'output-xml'     => true,
            'input-xml'      => true,
            'wrap'           => 0,
            'indent-spaces'  => 2,
            'newline'        => 'LF',
        ]);

        if ($tidyInstance === false) {
            throw new \Exception('Tidy: Error while loading the Sitemap xml with tidy_parse_file()');
        }

        if ($tidyInstance->errorBuffer) {
            throw new \Exception('Tidy: Errors while loading the Sitemap xml with tidy_parse_file(): ' . "\n" . $tidyInstance->errorBuffer);
        }

        $formatted = tidy_clean_repair(object: $tidyInstance);
        if ($formatted === false) {
            throw new \Exception('Tidy: Error while cleaning the Sitemap xml');
        }

        // save the formatted xml back to the temporary file
        file_put_contents($this->tempFile, (string) $tidyInstance);

        return $this->asString();
    }

    /**
     * Renders the sitemap index
     */
    private function renderSitemapIndex()
    {
        $this->append('<?xml version="1.0" encoding="UTF-8"?>');
        $this->append('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        foreach ($this->params['tags'] as $tag) {
            /** @var Sitemap $tag */
            
            $this->append('<sitemap>', 1);
            if (! empty($tag->url)) {
                $this->append('<loc>' . $this->format(url($tag->url)) . '</loc>', 2);
            }

            if (! empty($tag->lastModificationDate)) {
                $this->append('<lastmod>' . $tag->lastModificationDate->format(DateTime::ATOM) . '</lastmod>', 2);
            }

            $this->append('</sitemap>', 1);
        }

        $this->append('</sitemapindex>');
    }

    /**
     * Renders the sitemap
     */
    private function renderSitemap()
    {
        $this->append('<?xml version="1.0" encoding="UTF-8"?>');
        $this->append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"', 0, false);
        if ($this->params['hasImages']) {
            $this->append(' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', 0, false);
        }
        if ($this->params['hasNews']) {
            $this->append(' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"', 0, false);
        }
        $this->append('>', 0);

        foreach ($this->params['tags'] as $tag) {
            $this->renderUrl($tag);
        }

        $this->append('</urlset>', 0, false);
    }

    /**
     * Renders a Url tag
     *
     * @param Url $tag
     */
    private function renderUrl(Url $tag)
    {
        $this->append('<url>', 1);
        if (! empty($tag->url)) {
            $this->append('<loc>' . $this->format(url($tag->url)) . '</loc>', 2);
        }
        if (count($tag->alternates)) {
            foreach ($tag->alternates as $alternate) {
                $this->append('<xhtml:link rel="alternate" hreflang="' . $this->format($alternate->locale) . '" href="' . $this->format(url($alternate->url)) . '" />', 2);
            }
        }
        if (! empty($tag->lastModificationDate)) {
            $this->append('<lastmod>' . $tag->lastModificationDate->format(DateTime::ATOM) . '</lastmod>', 2);
        }
        if (! empty($tag->changeFrequency)) {
            $this->append('<changefreq>' . $this->format($tag->changeFrequency) . '</changefreq>', 2);
        }
        if (! empty($tag->priority)) {
            $this->append('<priority>' . number_format($tag->priority, 1) . '</priority>', 2);
        }
        if (count($tag->images)) {
            foreach ($tag->images as $image) {
                if (! empty($image->url)) {
                    $this->append('<image:image>', 2);
                    $this->append('<image:loc>' . url($image->url) . '</image:loc>', 3);
                    if (! empty($image->caption)) {
                        $this->append('<image:caption>' . $this->format($image->caption) . '</image:caption>', 3);
                    }
                    if (! empty($image->geo_location)) {
                        $this->append('<image:geo_location>' . $this->format($image->geo_location) . '</image:geo_location>', 3);
                    }
                    if (! empty($image->title)) {
                        $this->append('<image:title>' . $this->format($image->title) . '</image:title>', 3);
                    }
                    if (! empty($image->license)) {
                        $this->append('<image:license>' . $this->format($image->license) . '</image:license>', 3);
                    }
                    $this->append('</image:image>', 2);
                }
            }
        }
        if (count($tag->news)) {
            foreach ($tag->news as $new) {
                $this->append('<news:news>', 2);
                if (! empty($new->publication_date)) {
                    $this->append('<news:publication_date>' . $new->publication_date->format('Y-m-d') . '</news:publication_date>', 3);
                }
                if (! empty($new->title)) {
                    $this->append('<news:title>' . $this->format($new->title) . '</news:title>', 3);
                }
                if (! empty($new->name) || ! empty($new->language)) {
                    $this->append('<news:publication>', 3);
                    if (! empty($new->name)) {
                        $this->append('<news:name>' . $this->format($new->name) . '</news:name>', 4);
                    }

                    if (! empty($new->language)) {
                        $this->append('<news:language>' . $this->format($new->language) . '</news:language>', 4);
                    }
                    $this->append('</news:publication>', 3);
                }
                $this->append('</news:news>', 2);
            }
        }

        $this->append('</url>', 1);
    }

    /**
     * Returns the contents of the temporary file as a string
     *
     * @return string
     */
    private function asString() : string
    {
        if (!is_file($this->tempFile)) {
            throw new \Exception('The generated Sitemap temporary file does not exist');
        }

        if (!is_readable($this->tempFile)) {
            throw new \Exception('The generated Sitemap temporary file is not readable');
        }

        $contents = file_get_contents($this->tempFile);
        unlink($this->tempFile);

        if ($contents === false) {
            throw new \Exception('Error while reading the generated Sitemap temporary file');
        }
        if (empty($contents)) {
            throw new \Exception('The generated Sitemap temporary file is empty');
        }

        return $contents;
    }

    /**
     * Appends content to the temporary file
     *
     * @param string $content
     * @param string $indentLevel
     * @param string $newline
     */
    private function append(string $content, int $indentLevel = 0, bool $newline = true)
    {
        if (!is_file($this->tempFile)) {
            @touch($this->tempFile);
        }

        if (!is_file($this->tempFile)) {
            throw new \Exception('The temporary file does not exist');
        }

        if (!is_writable($this->tempFile)) {
            throw new \Exception('The temporary file is not writable');
        }

        $content = ($indentLevel) ? str_repeat(' ', $indentLevel * 2) . $content : $content;
        $content = ($newline) ? $content . "\n" : $content;
        $result = file_put_contents($this->tempFile, $content, FILE_APPEND);

        if ($result === false) {
            throw new \Exception('Error while writing to the temporary file');
        }
    }

    /**
     * Formats a tag text so that it does not contain invalid characters for the XML format.
     *
     * @param string|null $text
     *
     * @return string
     */
    private function format(?string $text = null) : string
    {
        $text = html_entity_decode($text ?? '', ENT_QUOTES | ENT_IGNORE, 'UTF-8');

        // remove any occurrence of UTF-8 encoding of a NO-BREAK SPACE codepoint, that we have decoded above
        $text = str_replace(chr(194).chr(160), ' ', $text);
        $text = trim(preg_replace('/\s\s+/', ' ', $text));

        return trim(htmlspecialchars($text, ENT_QUOTES | ENT_IGNORE, 'UTF-8'));
    }
}
