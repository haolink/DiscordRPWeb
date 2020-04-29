<?php

namespace RPWeb\Library;

use Pecee\Http\Response;
use RPWeb\Common\Kernel;

class Controller
{
    /**
     * Renders a template.
     *
     * @param string $template
     * @param array $templateVariables
     * @return string
     */
    protected function render(string $template, array $templateVariables = array()) {
        $renderedTwig = Kernel::getInstance()->getTwig()->render($template, $templateVariables);
        
        return $renderedTwig;
    }
}
