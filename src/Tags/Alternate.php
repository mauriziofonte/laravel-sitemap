<?php

namespace Mfonte\Sitemap\Tags;

class Alternate
{
    public string $locale;

    public string $url;

    public static function create(string $url, string $locale = '')
    {
        return new static($url, $locale);
    }

    public function __construct(string $url, $locale = '')
    {
        $this->setUrl($url);

        $this->setLocale($locale);
    }

    public function setLocale(string $locale = '')
    {
        $this->locale = $locale;

        return $this;
    }

    public function setUrl(string $url = '')
    {
        $this->url = $url;

        return $this;
    }
}