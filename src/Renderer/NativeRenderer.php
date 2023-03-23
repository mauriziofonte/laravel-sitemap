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

    public static function instance(array $params) : self
    {
        return new self($params);
    }

    public function __construct(array $params)
    {
        $this->params = $params;
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
                    $xml = $this->sitemapTemplate();

                    break;
                case 'sitemapIndex':
                    $xml = $this->sitemapIndexTemplate();

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
        
        if (! class_exists('\DOMDocument')) {
            return $xml;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_PARSEHUGE | LIBXML_NOERROR);
        $out = $dom->saveXML($dom->documentElement);

        if ($out === false) {
            throw new \Exception('DOMDocument: Error while prettifying the xml');
        }

        return $out;
    }

    private function sitemapIndexTemplate() : string
    {
        $template = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $template .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->params['tags'] as $tag) {
            /** @var Sitemap $tag */
            
            $template .= '<sitemap>';
            if (! empty($tag->url)) {
                $template .= '<loc>' . url($tag->url) . '</loc>';
            }

            if (! empty($tag->lastModificationDate)) {
                $template .= '<lastmod>' . $tag->lastModificationDate->format(DateTime::ATOM) . '</lastmod>';
            }

            $template .= '</sitemap>';
        }

        $template .= '</sitemapindex>';

        return $template;
    }

    private function sitemapTemplate() : string
    {
        $template = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $template .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        if ($this->params['hasImages']) {
            $template .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }
        if ($this->params['hasNews']) {
            $template .= ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"';
        }
        $template .= '>';

        foreach ($this->params['tags'] as $tag) {
            $template .= $this->urlTemplate($tag);
        }

        $template .= '</urlset>';

        return $template;
    }

    private function urlTemplate(Url $tag) : string
    {
        $template = '<url>';
        if (! empty($tag->url)) {
            $template .= '<loc>' . url($tag->url) . '</loc>';
        }
        if (count($tag->alternates)) {
            foreach ($tag->alternates as $alternate) {
                $template .= '<xhtml:link rel="alternate" hreflang="' . $alternate->locale . '" href="' . url($alternate->url) . '" />';
            }
        }
        if (! empty($tag->lastModificationDate)) {
            $template .= '<lastmod>' . $tag->lastModificationDate->format(DateTime::ATOM) . '</lastmod>';
        }
        if (! empty($tag->changeFrequency)) {
            $template .= '<changefreq>' . $tag->changeFrequency . '</changefreq>';
        }
        if (! empty($tag->priority)) {
            $template .= '<priority>' . number_format($tag->priority, 1) . '</priority>';
        }
        if (count($tag->images)) {
            foreach ($tag->images as $image) {
                if (! empty($image->url)) {
                    $template .= '<image:image>';
                    $template .= '<image:loc>' . url($image->url) . '</image:loc>';
                    if (! empty($image->caption)) {
                        $template .= '<image:caption>' . $image->caption . '</image:caption>';
                    }
                    if (! empty($image->geo_location)) {
                        $template .= '<image:geo_location>' . $image->geo_location . '</image:geo_location>';
                    }
                    if (! empty($image->title)) {
                        $template .= '<image:title>' . $image->title . '</image:title>';
                    }
                    if (! empty($image->license)) {
                        $template .= '<image:license>' . $image->license . '</image:license>';
                    }
                    $template .= '</image:image>';
                }
            }
        }
        if (count($tag->news)) {
            foreach ($tag->news as $new) {
                $template .= '<news:news>';
                if (! empty($new->publication_date)) {
                    $template .= '<news:publication_date>' . $new->publication_date->format('Y-m-d') . '</news:publication_date>';
                }
                if (! empty($new->title)) {
                    $template .= '<news:title>' . $new->title . '</news:title>';
                }
                if (! empty($new->name) || ! empty($new->language)) {
                    $template .= '<news:publication>';
                    if (! empty($new->name)) {
                        $template .= '<news:name>' . $new->name . '</news:name>';
                    }

                    if (! empty($new->language)) {
                        $template .= '<news:language>' . $new->language . '</news:language>';
                    }
                    $template .= '</news:publication>';
                }
                $template .= '</news:news>';
            }
        }

        $template .= '</url>';

        return $template;
    }
}
