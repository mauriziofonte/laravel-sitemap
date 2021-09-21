<?php

namespace Mfonte\Sitemap\Contracts;

use Mfonte\Sitemap\Tags\Url;

interface Sitemapable
{
    /**
     * @return Url|string|array
     */
    public function toSitemapTag();
}
