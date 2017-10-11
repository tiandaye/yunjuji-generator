<?php

/**
 * @Author: admin
 * @Date:   2017-09-30 21:08:55
 * @Last Modified by:   admin
 * @Last Modified time: 2017-10-10 17:22:35
 */

namespace Yunjuji\Generator\Generators;

use Yunjuji\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;

class RepositoryGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    /**
     * tian add
     * [$baseTemplateType 基本的模板类型]
     * @var [type]
     */
    private $baseTemplateType;

    /**
     * tian add
     * [$formMode form的模式]
     * @var [type]
     */
    private $formMode = '';

    /**
     * tian add
     * [$formModePrefix description]
     * @var [type]
     */
    private $formModePrefix = '';

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRepository;
        $this->fileName = $this->commandData->modelName.'Repository.php';

        /**
         * tian add start
         */
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');

        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        /**
         * tian add end
         */
    }

    public function generate()
    {
    	/**
    	 * tian comment start
    	 */
        // $templateData = get_template('repository', 'laravel-generator');

        // $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        // $searchables = [];

        // foreach ($this->commandData->fields as $field) {
        //     if ($field->isSearchable) {
        //         $searchables[] = "'".$field->name."'";
        //     }
        // }

        // $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        // $docsTemplate = get_template('docs.repository', 'laravel-generator');
        // $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
        // $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

        // $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);

        // FileUtil::createFile($this->path, $this->fileName, $templateData);

        // $this->commandData->commandComment("\nRepository created: ");
        // $this->commandData->commandInfo($this->fileName);
        /**
         * tian comment end
         */
        
        /**
         * tian add start
         */
        // 不同的模式选择不用的模板
        if ($this->formMode) {
            $templateData = yunjuji_get_template($this->formModePrefix . 'repository', $this->baseTemplateType);
        } else {
            $templateData = yunjuji_get_template('repository', $this->baseTemplateType);
        }

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->fields as $field) {
            if ($field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        $docsTemplate = get_template('docs.repository', 'laravel-generator');
        $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

        $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);

        // `linux` 和 `win` 有区别
        if (DIRECTORY_SEPARATOR != '\\') {
            FileUtil::createFile(str_replace('\\', DIRECTORY_SEPARATOR, $this->path), $this->fileName, $templateData);
        } else {
            FileUtil::createFile($this->path, $this->fileName, $templateData);
        }

        $this->commandData->commandComment("\nRepository created: ");
        $this->commandData->commandInfo($this->fileName);
        /**
         * tian add end
         */
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Repository file deleted: '.$this->fileName);
        }
    }
}
