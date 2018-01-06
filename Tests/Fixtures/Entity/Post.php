<?php

namespace wjb\RewireBundle\Tests\Fixtures\Entity;

class Post
{
    /** @var Category */
    private $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getId()
    {
        return 42;
    }

    public function getSlug()
    {
        return 'post-slug';
    }

    public function getCategory()
    {
        return $this->category;
    }
}

