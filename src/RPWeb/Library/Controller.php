<?php

namespace RPWeb\Library;

use Pecee\Http\Response;
use RPWeb\Common\Auth;
use RPWeb\Common\Kernel;
use RPWeb\Common\DiscordUser;

class Controller
{
    /**
     * Templatevariables.
     *
     * @var array
     */
    protected $templateVariables = array();

    /**
     * Sets a template variable.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function addTemplateVariable($key, $value) 
    {
        $this->templateVariables[$key] = $value;
    }

    /**
     * Sets multiple template variables.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function addTemplateVariables(array $variables) 
    {
        foreach ($variables as $key => $value) {
            $this->templateVariables[$key] = $value;
        }
    }

    /**
     * Renders a template.
     *
     * @param string $template
     * @param array $templateVariables
     * @return string
     */
    protected function render(string $template, array $templateVariables = array()) {
        $this->addTemplateVariable('user', $this->getUser());

        $this->addTemplateVariables($templateVariables);

        $renderedTwig = Kernel::getInstance()->getTwig()->render($template, $this->templateVariables);
        
        return $renderedTwig;
    }

    /**
     * Checks if a user is logged in.
     *
     * @return DiscordUser|null
     */
    protected function getUser() : ?DiscordUser
    {
        return Auth::getUser();
    }
}
