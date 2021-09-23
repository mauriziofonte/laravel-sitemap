<?php

namespace Mfonte\Sitemap\Test;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Mfonte\Sitemap\SitemapServiceProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;

abstract class TestCase extends OrchestraTestCase
{
    use MatchesSnapshots;

    protected Carbon $now;

    protected TemporaryDirectory $temporaryDirectory;

    public function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create('2016', '1', '1', '0', '0', '0');

        Carbon::setTestNow($this->now);

        $this->temporaryDirectory = (new TemporaryDirectory())->force()->create();
    }

    protected function getPackageProviders($app)
    {
        return [
            SitemapServiceProvider::class,
        ];
    }
}