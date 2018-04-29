<?php
/**
 * Created by 4/16/18 6:02 PM.
 * @author Mediengstalt Heimbach - Johannes Heimbach
 */

class PicoContentVars extends AbstractPicoPlugin
{

    const API_VERSION = 2;
    private $variables = [];

    private $defaultConfig = [
        'content_vars' => [
            'config' => [
                'folder' => 'vars/',
                'file' => 'vars.yml'
            ]
        ]
    ];


    public function onConfigLoaded($config)
    {
        $config += $this->defaultConfig;

        $loadConfigClosure = $this->loadConfigClosure();

        $env = $this->getPico()->getConfig('env', null);

        if ($env != null) {
            $this->variables['env'] = $env;
        }

        $configFile = $this->getConfigFile($config, $env);

        if (file_exists($configFile)) {
            $this->variables += $loadConfigClosure($configFile);
        }
    }

    /**
     * @param string $markdown
     */
    public function onContentPrepared(&$markdown)
    {
        foreach ($this->variables as $key => $variable) {
            $markdown = str_replace('%' . $key . '%', $variable, $markdown);
        }
    }

    public function onPageRendering(&$twigTemplate, &$twigVariables)
    {
        $twigVariables = array_merge($twigVariables, $this->variables);
    }

    /**
     * @param array $config
     * @param null|string $env
     * @return string
     */
    protected function getConfigFile($config, $env = null)
    {
        $folder = $config['content_vars']['config']['folder'];
        $filename = $folder . $config['content_vars']['config']['file'];

        if ($env != null) {
            //set environment vars file if exists
            $envFilename = $folder . 'vars.' . $env . '.yml';
            $filename = file_exists($this->getPico()->getConfigDir() . $envFilename) ? $envFilename : $filename;
        }

        return $this->getPico()->getConfigDir() . $filename;
    }

    /**
     * @return Closure
     */
    protected function loadConfigClosure()
    {
        $yamlParser = $this->getPico()->getYamlParser();

        $loadConfigClosure = function ($configFile) use ($yamlParser) {
            $yaml = file_get_contents($configFile);
            $config = $yamlParser->parse($yaml);
            return is_array($config) ? $config : array();
        };

        return $loadConfigClosure;
    }
}
