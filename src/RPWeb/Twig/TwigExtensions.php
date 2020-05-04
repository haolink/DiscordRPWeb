<?php

namespace RPWeb\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension
{
    /**
     * Twig functions required in the environment.
     *
     * @return array
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('url', array($this, 'generateUrl')),
        ];
    }

    /**
     * Generates a URL string.
     *
     * @param string $routeString
     * @param array $parameters
     * @param array $getParameters
     * @return string
     */
    public function generateUrl($routeString, $parameters = array(), $getParameters = array()) : string
    {
        return url($routeString, $parameters, $getParameters);
    }
}
