<?php

namespace wjb\RewireBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RewireParams
{
    public $requires = [];

    public $rewire = [];
}

