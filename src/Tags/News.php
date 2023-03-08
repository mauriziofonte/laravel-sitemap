<?php

namespace Mfonte\Sitemap\Tags;

use Carbon\Carbon;
use DateTimeInterface;

class News
{
    /** @var string */
    public $name;

    /** @var string */
    public $language;

    /** @var Carbon */
    public $publication_date;
    
    /** @var string */
    public $title;

    public static function create(
        string $name,
        string $language,
        ?DateTimeInterface $publication_date,
        string $title
    ) {
        return new static($name, $language, $publication_date, $title);
    }

    final public function __construct(
        string $name,
        string $language,
        ?DateTimeInterface $publication_date,
        string $title
    ) {
        $this->setName($name);
        $this->setLanguage($language);
        $this->setPublicationDate($publication_date);
        $this->setTitle($title);
    }

    private function normalize(string $property)
    {
        return htmlentities(strip_tags($property), ENT_NOQUOTES, 'UTF-8');
    }

    public function setName(string $name)
    {
        $this->name = $this->normalize($name);

        return $this;
    }

    public function setLanguage(string $language)
    {
        $this->language = $this->normalize($language);

        return $this;
    }
    
    public function setPublicationDate(?DateTimeInterface $publication_date = null)
    {
        if ($publication_date) {
            $this->publication_date = Carbon::instance($publication_date);
        }

        return $this;
    }

    public function setTitle(string $title)
    {
        $this->title = $this->normalize($title);

        return $this;
    }
}
