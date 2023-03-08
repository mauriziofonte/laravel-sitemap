<?php

namespace Mfonte\Sitemap\Test;

use Mfonte\Sitemap\Tags\Image;

class ImageTest extends TestCase
{
    protected Image $image;

    public function setUp(): void
    {
        parent::setUp();

        $this->image = new Image('defaultUrl');
    }

    /** @test */
    public function it_provides_a_create_method()
    {
        $image = Image::create('anotherDefaultUrl');

        $this->assertEquals('anotherDefaultUrl', $image->url);
    }

    /** @test */
    public function url_can_be_set()
    {
        $this->image->setUrl('testUrl');

        $this->assertEquals('testUrl', $this->image->url);
    }

    /** @test */
    public function caption_can_be_set()
    {
        $this->image->setCaption('a_caption');

        $this->assertEquals('a_caption', $this->image->caption);
    }

    /** @test */
    public function geolocation_can_be_set()
    {
        $this->image->setGeolocation('some_geolocation');

        $this->assertEquals('some_geolocation', $this->image->geo_location);
    }

    /** @test */
    public function title_can_be_set()
    {
        $this->image->setTitle('a_beautiful_title');

        $this->assertEquals('a_beautiful_title', $this->image->title);
    }

    /** @test */
    public function license_can_be_set()
    {
        $this->image->setLicense('some_random_photographer');

        $this->assertEquals('some_random_photographer', $this->image->license);
    }
}
