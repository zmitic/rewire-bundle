<?php

namespace wjb\RewireBundle\Decorator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RoutingDecorator implements RouterInterface
{
    private $rewireConfig = [];

    /** @var PropertyAccessor  */
    private $propertyAccessor;

    private $router;

    public function __construct(RouterInterface $router, $cacheDir)
    {
        $this->router = $router;
        if (null !== $cacheDir && file_exists($cacheFilename = $cacheDir.'/wjb_rewire.php')) {
            $this->rewireConfig = require $cacheFilename;
        }
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param RequestContext $context
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

    /**
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     *
     * @return string
     *
     * @throws UnexpectedTypeException
     * @throws InvalidArgumentException
     * @throws RouteNotFoundException
     * @throws MissingMandatoryParametersException
     * @throws InvalidParameterException
     * @throws AccessException
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $this->rewireParamsForRoute($name, $parameters);

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array
     * @throws ResourceNotFoundException|NoConfigurationException|MethodNotAllowedException
     */
    public function match($pathinfo)
    {
        return $this->router->match($pathinfo);
    }


    /**
     * @param string $routeName
     * @param array $parameters
     * @throws UnexpectedTypeException|InvalidArgumentException|AccessException
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
     *
     * @return bool
     *
     * @throws UnexpectedTypeException|InvalidArgumentException|AccessException
     */
    private function rewireParamsFromConfig(array &$parameters, array $config)
    {
        $requiredParams = (array)$config['requires'];

        // check if all required params for rewiring are provided
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

