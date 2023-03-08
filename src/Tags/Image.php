<?php

namespace Mfonte\Sitemap\Tags;

class Image
{
    /** @var string */
    public $url;

    /** @var string */
    public $caption;

    /** @var string */
    public $geo_location;
    
    /** @var string */
    public $title;

    /** @var string */
    public $license;

    public static function create(
        string $url,
        string $caption = '',
        string $geo_location = '',
        string $title = '',
        string $license = ''
    ) {
        return new static($url, $caption, $geo_location, $title, $license);
    }

    final public function __construct(
        string $url,
        string $caption = '',
        string $geo_location = '',
        string $title = '',
        string $license = ''
    ) {
        $this->setUrl($url);
        $this->setCaption($caption);
        $this->setGeolocation($geo_location);
        $this->setTitle($title);
        $this->setLicense($license);
    }

    private function normalize(string $property)
    {
        return htmlentities(strip_tags($property), ENT_NOQUOTES, 'UTF-8');
    }

    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function setCaption(string $caption = '')
    {
        $this->caption = $this->normalize($caption);

        return $this;
    }

    public function setGeolocation(string $geo_location = '')
    {
        $this->geo_location = $this->normalize($geo_location);

        return $this;
    }

    public function setTitle(string $title = '')
    {
        $this->title = $this->normalize($title);

        return $this;
    }

    public function setLicense(string $license = '')
    {
        $this->license = $this->normalize($license);

        return $this;
    }
}
