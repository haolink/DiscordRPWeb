<?php

namespace RPWeb\Twig;

use RPWeb\Common\Kernel;
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
            new TwigFunction('config', array($this, 'getConfig')),
            new TwigFunction('csrf_token', array($this, 'generateCsrf')),
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

    /**
     * Generates a CSRF token.
     *
     * @param string $string
     * @return string
     */
    public function generateCsrf($string) : string
    {
        return csrf_token($string);
    }

    /**
     * Reads the system configuration.
     *
     * @param string $string
     * @return array
     */
    public function getConfig() : array
    {
        return Kernel::getInstance()->getConfig();
    }
}
