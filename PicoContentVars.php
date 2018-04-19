<?php
/**
 * Created by 4/16/18 6:02 PM.
 * @author Mediengstalt Heimbach - Johannes Heimbach
 */

class PicoContentVars extends AbstractPicoPlugin
{

    const API_VERSION = 2;
    private $variables = [];


    public function onConfigLoaded($config)
    {
        $yamlParser = $this->getPico()->getYamlParser();

        $loadConfigClosure = function ($configFile) use ($yamlParser) {
            $yaml = file_get_contents($configFile);
            $config = $yamlParser->parse($yaml);
            return is_array($config) ? $config : array();
        };

        if ($this->getPico()->getConfig('env', null) != null) {
            $this->variables['env'] = $this->getPico()->getConfig('env');
        }

        $configFile = $this->getConfigFile($config);

        if (file_exists($configFile)) {
            $this->variables += $loadConfigClosure($configFile);
        }
    }

    /**
     * @param string $markdown
     */
    public function onContentPrepared(&$markdown)
    {
        $variables = [];
        foreach ($this->variables as $key => $variable) {
            $variables['%' . $key . '%'] = $variable;
        }

        $markdown = str_replace('%env%', $variables['%env%'], $markdown);
        $markdown = str_replace(array_keys($variables), array_values($variables), $markdown);
    }

    public function onPageRendering($twigTemplate, $twigVariables)
    {
        $twigVariables = array_merge($twigVariables, $this->variables);
    }

    /**
     * @return string
     */
    protected function getConfigFile($config)
    {
        $file = 'vars.yml';

        if (array_key_exists('contentvars', $config) &&
            is_array($config['contentvars']) &&
            array_key_exists('file', $config['contentvars'])) {
            $file = $config['contentvars']['file'];
        }

        return $this->getPico()->getConfigDir() . $file;
    }

}
