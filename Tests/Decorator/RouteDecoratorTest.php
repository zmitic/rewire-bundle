<?php

namespace wjb\RewireBundle\Tests\Decorator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use wjb\RewireBundle\Decorator\RoutingDecorator;
use wjb\RewireBundle\Fixtures\Controller\FooController;
use wjb\RewireBundle\Fixtures\Entity\Category;
use wjb\RewireBundle\Fixtures\Entity\Post;

class RouteDecoratorTest extends KernelTestCase
{
    /** @var RouterInterface */
    private $router;

    /** @var ContainerInterface */
    private $container;

    /** @var Post */
    private $post;

    protected function setUp()
    {
        require_once __DIR__.'/../../Fixtures/TestKernel.php';

        $kernel = new \TestKernel('test', true);
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $this->container->get('cache_warmer')->warmUp($kernel->getCacheDir());
        $this->router = $this->container->get('router');

        $this->post = new Post(new Category());
    }

    public function testDecorationSuccessful()
    {
        $this->assertInstanceOf(RoutingDecorator::class, $this->router);
    }

    /**
     * Assert old way is still working
     */
    public function testPlainRouteMatch()
    {
        $path = $this->router->generate('rewire_post_id', ['id' => 42]);
        $this->assertEquals('/post/by_id/42', $path);
    }

    /**
     * Test rewiring by id only
     *
     * @see FooController::postById()
     */
    public function testRewireId()
    {
        $post = $this->post;

        $path = $this->router->generate('rewire_post_id', ['post' => $post]);
        $this->assertEquals('/post/by_id/42', $path);
    }

    /**
     * @see FooController::postBySlug()
     */
    public function testRewireBySlug()
    {
        $post = $this->post;

        $path = $this->router->generate('rewire_post_slug', ['post' => $post]);
        $this->assertEquals('/post/by_slug/post-slug', $path);
    }

    /**
     * @see FooController::complex()
     */
    public function testComplexRouteRewiring()
    {
        $post = $this->post;

        $path = $this->router->generate('rewire_complex', ['post' => $post]);
        $this->assertEquals('/post/complex/category-slug/post-slug', $path);
    }
}

