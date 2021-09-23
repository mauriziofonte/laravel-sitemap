<?php

namespace Mfonte\Sitemap\Tags;

class Alternate
{
    /** @var string */
    public $url;

    /** @var string */
    public $locale;

    public static function create(string $url, string $locale = '')
    {
        return new static($url, $locale);
    }

    public function __construct(string $url, $locale = '')
    {
        $this->setUrl($url);

        $this->setLocale($locale);
    }

    public function setUrl(string $url = '')
    {
        $this->url = $url;

        return $this;
    }

    public function setLocale(string $locale = '')
    {
        $this->locale = $locale;

        return $this;
    }
}