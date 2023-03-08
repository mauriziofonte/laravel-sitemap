<?php

namespace Mfonte\Sitemap\Test;

use Carbon\Carbon;
use Mfonte\Sitemap\Tags\News;

class NewsTest extends TestCase
{
    protected News $news;

    public function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::now();
        Carbon::setTestNow($this->now);

        $this->news = new News('Name', 'Language', $this->now, 'Title');
    }

    /** @test */
    public function name_can_be_set()
    {
        $this->news->setName('testName');

        $this->assertEquals('testName', $this->news->name);
    }

    /** @test */
    public function language_can_be_set()
    {
        $this->news->setLanguage('testLang');

        $this->assertEquals('testLang', $this->news->language);
    }

    /** @test */
    public function publication_date_can_be_set()
    {
        $carbon = Carbon::now()->subDay();
        $this->news->setPublicationDate($carbon);

        $this->assertEquals($carbon->toAtomString(), $this->news->publication_date->toAtomString());
    }

    /** @test */
    public function title_can_be_set()
    {
        $this->news->setTitle('a_beautiful_title');

        $this->assertEquals('a_beautiful_title', $this->news->title);
    }
}
