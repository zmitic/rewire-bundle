<?php

namespace wjb\RewireBundle\Decorator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RoutingExtensionDecorator implements RouterInterface
{
    private $rewireConfig;

    /** @var PropertyAccessor  */
    private $propertyAccessor;
    private $router;

    public function __construct(RouterInterface $router, $cacheDir)
    {
        $this->router = $router;
        $this->rewireConfig = require $cacheDir.'/wjb_rewire.php';
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return void
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * @return RequestContext The context
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * @return RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $this->rewireParamsForRoute($name, $parameters);

        return $this->router->generate($name, $parameters, $referenceType);
    }

    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }


    /**
     * @param string $routeName
     * @param array $parameters
     * @throws AccessException
     */
    private function rewireParamsForRoute($routeName, array &$parameters)
    {
        if (!isset($this->rewireConfig[$routeName])) {
            return;
        }

        $configurations = $this->rewireConfig[$routeName];
        // try rewiring for each defined configuration value
        foreach ((array)$configurations as $configuration) {
            if ($this->rewireParamsFromConfig($parameters, $configuration)) {
                // stop as soon as rewiring is done
                return;
            }
        }
    }

    /**
     * @param array $parameters
     * @param array $config
     * @return bool
     * @throws AccessException
     */
    private function rewireParamsFromConfig(array &$parameters, array $config)
    {
        $requiredParams = (array)$config['requires'];

        foreach ($requiredParams as $requiredParam) {
            if (!isset($parameters[$requiredParam])) {
                return false;
            }
        }

        foreach ((array)$config['rewire'] as $target => $expression) {
            $parameters[$target] = $this->propertyAccessor->getValue((object)$parameters, $expression);
        }

        foreach ($requiredParams as $requiredParam) {
            unset($parameters[$requiredParam]);
        }

        return true;
    }
}

