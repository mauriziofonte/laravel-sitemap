<?php

namespace Mfonte\Sitemap\Test;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mfonte\Sitemap\Contracts\Sitemapable;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SitemapTest extends TestCase
{
    protected Sitemap $sitemap;

    public function setUp(): void
    {
        parent::setUp();

        $this->sitemap = new Sitemap();
    }

    /** @test */
    public function it_provides_a_create_method()
    {
        $sitemap = Sitemap::create();

        $this->assertInstanceOf(Sitemap::class, $sitemap);
    }

    /** @test */
    public function it_can_render_an_empty_sitemap()
    {
        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function it_can_write_a_sitemap_to_a_file()
    {
        $path = $this->temporaryDirectory->path('test.xml');

        $this->sitemap->writeToFile($path);

        $this->assertMatchesXmlSnapshot(file_get_contents($path));
    }

    /** @test */
    public function it_can_write_a_sitemap_to_a_storage_disk()
    {
        Storage::fake('sitemap');
        $this->sitemap->writeToDisk('sitemap', 'sitemap.xml');

        $this->assertMatchesXmlSnapshot(Storage::disk('sitemap')->get('sitemap.xml'));
    }

    /** @test */
    public function an_url_string_can_be_added_to_the_sitemap()
    {
        $this->sitemap->add('/home');

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function a_url_string_can_not_be_added_twice_to_the_sitemap()
    {
        $this->sitemap->add('/home');
        $this->sitemap->add('/home');

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function an_url_with_an_alternate_can_be_added_to_the_sitemap()
    {
        $url = Url::create('/home')
            ->addAlternate('/thuis', 'nl')
            ->addAlternate('/maison', 'fr');

        $this->sitemap->add($url);

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function an_url_object_can_be_added_to_the_sitemap()
    {
        $this->sitemap->add(Url::create('/home'));

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function multiple_urls_can_be_added_to_the_sitemap()
    {
        $this->sitemap
            ->add(Url::create('/home'))
            ->add(Url::create('/contact'));

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function it_can_render_an_url_with_all_its_set_properties()
    {
        $this->sitemap
            ->add(
                Url::create('/home')
                ->setLastModificationDate($this->now->subDay())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1)
            );

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function it_can_determine_if_it_contains_a_given_url()
    {
        $this->sitemap
            ->add('/page1')
            ->add('/page2')
            ->add('/page3');

        $this->assertTrue($this->sitemap->hasUrl('/page2'));
    }

    /** @test */
    public function it_can_determine_if_it_contains_urls_with_images()
    {
        $url = new Url('/page10');
        $url->addImage('/imageUrl');

        $this->sitemap->add($url);

        $this->assertTrue($this->sitemap->hasImages());
    }

    /** @test */
    public function it_can_determine_if_it_contains_urls_with_news()
    {
        $url = new Url('/page10');
        $url->addNews('defaultName', 'defaultLanguage', Carbon::now()->subDays(3), 'defaultTitle');

        $this->sitemap->add($url);

        $this->assertTrue($this->sitemap->hasNews());
    }

    /** @test */
    public function it_can_determine_if_it_contains_urls_with_images_and_news()
    {
        $url = new Url('/page10');
        $url->addNews('defaultName', 'defaultLanguage', Carbon::now()->subDays(3), 'defaultTitle');
        $url->addImage('/imageUrl');

        $this->sitemap->add($url);

        $this->assertTrue($this->sitemap->hasImages());
        $this->assertTrue($this->sitemap->hasNews());
    }

    /** @test */
    public function it_can_get_a_specific_url()
    {
        $this->sitemap->add('/page1');
        $this->sitemap->add('/page2');

        $url = $this->sitemap->getUrl('/page2');

        $this->assertInstanceOf(Url::class, $url);
        $this->assertSame('/page2', $url->url);
    }

    /** @test */
    public function it_returns_null_when_getting_a_non_existing_url()
    {
        $this->assertNull($this->sitemap->getUrl('/page1'));

        $this->sitemap->add('/page1');

        $this->assertNotNull($this->sitemap->getUrl('/page1'));

        $this->assertNull($this->sitemap->getUrl('/page2'));
    }

    /** @test */
    public function a_url_object_can_not_be_added_twice_to_the_sitemap()
    {
        $this->sitemap->add(Url::create('/home'));
        $this->sitemap->add(Url::create('/home'));

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function an_instance_can_return_a_response()
    {
        $this->sitemap->add(Url::create('/home'));

        $this->assertInstanceOf(Response::class, $this->sitemap->toResponse(new Request));
    }

    /** @test */
    public function multiple_urls_can_be_added_in_one_call()
    {
        $this->sitemap->add([
            Url::create('/'),
            '/home',
            Url::create('/home'), // filtered
        ]);

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function sitemapable_object_can_be_added()
    {
        $this->sitemap
            ->add(new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return '/';
                }
            })
            ->add(new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return Url::create('/home');
                }
            })
            ->add(new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return [
                        'blog/post-1',
                        Url::create('/blog/post-2'),
                    ];
                }
            });

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }

    /** @test */
    public function sitemapable_objects_can_be_added()
    {
        $this->sitemap->add(collect([
            new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return 'blog/post-1';
                }
            },
            new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return 'blog/post-2';
                }
            },
            new class implements Sitemapable {
                public function toSitemapTag()
                {
                    return 'blog/post-3';
                }
            },
        ]));

        $this->assertMatchesXmlSnapshot($this->sitemap->render());
    }
}
