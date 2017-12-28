<?php

namespace wjb\RewireBundle\Cache;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use wjb\RewireBundle\Annotation\RewireParams;

class RewireParamsAnnotationsWarmer extends CacheWarmer
{
    /** @var RouterInterface */
    private $router;

    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(RouterInterface $router, Reader $annotationReader)
    {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDir): void
    {
        $routes = $this->router->getRouteCollection()->all();

        $cache = [];
        foreach ($routes as $routeName => $config) {
            $annotations = $this->getRewireParams($config);
            foreach ($annotations as $annotation) {
                $cache[$routeName][] = [
                    'requires' => $annotation->requires,
                    'rewire' => $annotation->rewire,
                ];
            }
        }
        $cacheValue = sprintf('<?php return %s;', var_export($cache, true));

        $this->writeCacheFile($cacheDir.'/wjb_rewire.php', $cacheValue);
    }

    /**
     * @return RewireParams[]
     */
    private function getRewireParams(Route $route): array
    {
        $defaults = $route->getDefaults();
        $controller = $defaults['_controller'];
        if (strpos($controller, '::') === false) {
            return [];
        }

        [$controllerClass, $method] = explode('::', $controller);

        $methodReflection = new \ReflectionMethod($controllerClass, $method);
        $methodAnnotations = $this->annotationReader->getMethodAnnotations($methodReflection);

        $results = [];
        foreach ($methodAnnotations as $annotation) {
            if ($annotation instanceof RewireParams) {
                $results[] = $annotation;
            }
        }

        return $results;
    }
}

