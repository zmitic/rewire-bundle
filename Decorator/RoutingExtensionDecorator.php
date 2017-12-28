<?php

namespace wjb\RewireBundle\Decorator;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RoutingExtensionDecorator extends RoutingExtension
{
    private $rewireConfig;

    /** @var PropertyAccessor  */
    private $propertyAccessor;

    public function __construct(UrlGeneratorInterface $generator, string $cacheDir)
    {
        $this->rewireConfig = require $cacheDir.'/wjb_rewire.php';
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        parent::__construct($generator);
    }

    public function getPath($name, $parameters = array(), $relative = false): string
    {
        $this->rewireParamsForRoute($name, $parameters);

        return parent::getPath($name, $parameters, $relative);
    }

    public function getUrl($name, $parameters = array(), $schemeRelative = false): string
    {
        $this->rewireParamsForRoute($name, $parameters);

        return parent::getUrl($name, $parameters, $schemeRelative);
    }

    private function rewireParamsForRoute(string $routeName, array &$parameters): void
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

    private function rewireParamsFromConfig(array &$parameters, array $config): bool
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

