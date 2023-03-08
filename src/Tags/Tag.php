<?php

namespace Mfonte\Sitemap\Tags;

abstract class Tag
{
    /** @var string */
    public $url;
    
    public function getType(): string
    {
        return mb_strtolower(class_basename(static::class));
    }
}
