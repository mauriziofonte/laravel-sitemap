# Generate sitemaps with Laravel, compatible with Images Sitemap and News Sitemap

[This package has been forked from spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap), to remove support for `SitemapGenerator`, remove installation requirement for PHP 8, and add support for **Images Sitemaps** and **News Sitemaps**.

This package can generate a valid sitemap by writing your own custom logic for the sitemap structure, via the API provided by this package.

> Heads up! This package requires _PHP 8.1_ minimum and _Laravel 9_ or _Laravel 10_. 
> For **PHP 7.4 and Laravel 8 compatibility** refer to **v1.1**

[![Latest Stable Version](https://poser.pugx.org/mfonte/laravel-sitemap/v/stable)](https://packagist.org/packages/mfonte/laravel-sitemap)
[![Total Downloads](https://poser.pugx.org/mfonte/laravel-sitemap/downloads)](https://packagist.org/packages/mfonte/laravel-sitemap)
[![Coverage Status](https://scrutinizer-ci.com/g/mauriziofonte/laravel-sitemap/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mauriziofonte/laravel-sitemap/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mauriziofonte/laravel-sitemap/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mauriziofonte/laravel-sitemap/)

## Installation

For Laravel 9 or 10 (min. PHP 8.1):

`composer require mfonte/laravel-sitemap`

For Laravel 8:

`composer require mfonte/laravel-sitemap "^1.1"`

## Creating sitemaps

You can only create your sitemap manually:

```php
use Carbon\Carbon;
use Mfonte\Sitemap\Sitemap;
use Mfonte\Sitemap\Tags\Url;

Sitemap::create()

    ->add(
        Url::create('/home')
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
        ->setPriority(0.1)
        ->addImage('/path/to/image', 'A wonderful Caption')
        ->addNews('A long story short', 'en', Carbon::yesterday(), 'Sitemaps are this great!')
    )

   ->add(...)

   ->writeToFile($path);
```

The sitemap generator can automatically understand what type of items you placed inside the sitemap, and create a valid schema accordingly. This is an example of a sitemap header with images and news:

```xml
<?xml version="1.0" encoding="UTF-8"?>\n
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <url>
        <loc>http://localhost/page10</loc>\n
        <lastmod>2016-01-01T00:00:00+00:00</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
        <image:image>
            <image:loc>http://localhost/imageUrl</image:loc>
        </image:image>
        <news:news>
            <news:publication_date>2015-12-29</news:publication_date>
            <news:title>defaultTitle</news:title>
            <news:publication>
                <news:name>defaultName</news:name>
                <news:language>defaultLanguage</news:language>
            </news:publication>
        </news:news>
    </url>
</urlset>
```

You can also add your models directly by implementing the `\Mfonte\Sitemap\Contracts\Sitemapable` interface.

```php
use Mfonte\Sitemap\Contracts\Sitemapable;
use Mfonte\Sitemap\Tags\Url;

class Post extends Model implements Sitemapable
{
    public function toSitemapTag(): Url | string | array
    {
        return route('blog.post.show', $this);
    }
}
```

Now you can add a single post model to the sitemap or even a whole collection.
```php
use Mfonte\Sitemap\Sitemap;

Sitemap::create()
    ->add($post)
    ->add(Post::all());
```

This way you can add all your pages super fast without the need to crawl them all.

## Installation

First, install the package via composer:

``` bash
composer require mfonte/laravel-sitemap
```

The package will automatically register itself.

## Usage
### Manually creating a sitemap

You can also create a sitemap fully manual:

```php
use Carbon\Carbon;

Sitemap::create()
   ->add('/page1')
   ->add('/page2')
   ->add(Url::create('/page3')->setLastModificationDate(Carbon::create('2016', '1', '1')))
   ->writeToFile($sitemapPath);
```

### Creating a sitemap index
You can create a sitemap index:
```php
use Mfonte\Sitemap\SitemapIndex;

SitemapIndex::create()
    ->add('/pages_sitemap.xml')
    ->add('/posts_sitemap.xml')
    ->writeToFile($sitemapIndexPath);
```

You can pass a `Mfonte\Sitemap\Tags\Sitemap` object to manually set the `lastModificationDate` property.

```php
use Mfonte\Sitemap\SitemapIndex;
use Mfonte\Sitemap\Tags\Sitemap;

SitemapIndex::create()
    ->add('/pages_sitemap.xml')
    ->add(Sitemap::create('/posts_sitemap.xml')
        ->setLastModificationDate(Carbon::yesterday()))
    ->writeToFile($sitemapIndexPath);
```

the generated sitemap index will look similar to this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <sitemap>
      <loc>http://www.example.com/pages_sitemap.xml</loc>
      <lastmod>2016-01-01T00:00:00+00:00</lastmod>
   </sitemap>
   <sitemap>
      <loc>http://www.example.com/posts_sitemap.xml</loc>
      <lastmod>2015-12-31T00:00:00+00:00</lastmod>
   </sitemap>
</sitemapindex>
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Credits

- [Original package published by Spatie](https://github.com/spatie/laravel-sitemap)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. **This package has been forked from https://github.com/spatie/laravel-sitemap and the relative license file has been migrated into this repository as-it-is**.
