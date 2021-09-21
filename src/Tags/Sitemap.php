<?php

namespace Mfonte\Sitemap\Tags;

use Carbon\Carbon;
use DateTimeInterface;

class Sitemap extends Tag
{
    public string $url;

    /** @var Carbon */
    public $lastModificationDate;

    public static function create(string $url)
    {
        return new static($url);
    }

    public function __construct(string $url)
    {
        $this->url = $url;

        $this->lastModificationDate = Carbon::now();
    }

    public function setUrl(string $url = '')
    {
        $this->url = $url;

        return $this;
    }

    public function setLastModificationDate(DateTimeInterface $lastModificationDate)
    {
        $this->lastModificationDate = Carbon::instance($lastModificationDate);

        return $this;
    }

    public function path(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?? '';
    }
}