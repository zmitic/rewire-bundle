<?php

namespace wjb\RewireBundle\Fixtures\Controller;

use Symfony\Component\Routing\Annotation\Route;
use wjb\RewireBundle\Annotation\RewireParams;

class FooController
{
    /**
     * @Route("/post/by_id/{id}", name="rewire_post_id")
     *
     * @RewireParams(requires={"post"}, rewire={"id"="post.id"})
     */
    public function postById($id)
    {
    }

    /**
     * @Route("/post/by_slug/{slug}", name="rewire_post_slug")
     *
     * @RewireParams(requires={"post"}, rewire={"slug"="post.slug"})
     */
    public function postBySlug($slug)
    {
    }

    /**
     * @Route("/post/complex/{category_slug}/{slug}", name="rewire_complex")
     *
     * @RewireParams(requires={"post"}, rewire={"category_slug"="post.category.slug", "slug"="post.slug"})
     */
    public function complex($slug)
    {
    }

}

