<?php

namespace Mfonte\Sitemap\Tags;

use Carbon\Carbon;
use DateTimeInterface;

class Url extends Tag
{
    const CHANGE_FREQUENCY_ALWAYS = 'always';
    const CHANGE_FREQUENCY_HOURLY = 'hourly';
    const CHANGE_FREQUENCY_DAILY = 'daily';
    const CHANGE_FREQUENCY_WEEKLY = 'weekly';
    const CHANGE_FREQUENCY_MONTHLY = 'monthly';
    const CHANGE_FREQUENCY_YEARLY = 'yearly';
    const CHANGE_FREQUENCY_NEVER = 'never';

    /** @var string */
    public $url;

    /** @var Carbon */
    public $lastModificationDate;

    /** @var string */
    public $changeFrequency;

    /** @var float */
    public $priority = 0.8;

    /** @var \Mfonte\Sitemap\Tags\Alternate[] */
    public $alternates = [];

    /** @var \Mfonte\Sitemap\Tags\Image[] */
    public $images = [];

    /** @var \Mfonte\Sitemap\Tags\News[] */
    public $news = [];

    public static function create(string $url)
    {
        return new static($url);
    }

    final public function __construct(string $url)
    {
        $this->url = $url;

        $this->lastModificationDate = Carbon::now();

        $this->changeFrequency = static::CHANGE_FREQUENCY_DAILY;
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

    public function setChangeFrequency(string $changeFrequency)
    {
        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    public function setPriority(float $priority)
    {
        $this->priority = max(0, min($priority, 1));

        return $this;
    }

    public function addAlternate(string $url, string $locale = '')
    {
        $this->alternates[] = new Alternate($url, $locale);

        return $this;
    }

    public function addImage(
        string $url,
        string $caption = '',
        string $geo_location = '',
        string $title = '',
        string $license = ''
    ) {
        $this->images[] = new Image($url, $caption, $geo_location, $title, $license);

        return $this;
    }

    public function addNews(
        string $name,
        string $language,
        ?DateTimeInterface $publication_date,
        string $title
    ) {
        $this->news[] = new News($name, $language, $publication_date, $title);

        return $this;
    }

    public function path(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?? '';
    }

    /**
     * @param int|null $index
     *
     * @return array|string|null
     */
    public function segments(?int $index = null)
    {
        $segments = collect(explode('/', $this->path()))
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values()
            ->toArray();

        if (! is_null($index)) {
            return $this->segment($index);
        }

        return $segments;
    }

    public function segment(int $index): ?string
    {
        return $this->segments()[$index - 1] ?? null;
    }
}
