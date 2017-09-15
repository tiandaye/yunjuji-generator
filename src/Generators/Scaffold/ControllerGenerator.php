<?php

namespace Yunjuji\Generator\Generators\Scaffold;

use Yunjuji\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class ControllerGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathController;

        // tian comment
        // $this->templateType = config('infyom.laravel_generator.templates', 'core-templates');
        
        /**
         * tian add start
         */
        $this->templateType = config('yunjuji.generator.templates.extend', 'core-templates');
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        /**
         * tian add end
         */
        
        $this->fileName = $this->commandData->modelName.'Controller.php';
    }

    /**
     * [generate 【产生】]
     * @return [type] [description]
     */
    public function generate()
    {
        // tian comment
        // if ($this->commandData->getAddOn('datatables')) {
        //     $templateData = get_template('scaffold.controller.datatable_controller', 'laravel-generator');

        //     $this->generateDataTable();
        // } else {
        //     $templateData = get_template('scaffold.controller.controller', 'laravel-generator');

        //     $paginate = $this->commandData->getOption('paginate');

        //     if ($paginate) {
        //         $templateData = str_replace('$RENDER_TYPE$', 'paginate('.$paginate.')', $templateData);
        //     } else {
        //         $templateData = str_replace('$RENDER_TYPE$', 'all()', $templateData);
        //     }
        // }

        /**
         * tian add start
         */
        // 不同的模式选择不用的模板
        if ($mode = $this->commandData->getOption('formMode')) {
            $templateData = yunjuji_get_template($mode. '.scaffold.controller.controller', $this->baseTemplateType);
        } else {
            $templateData = yunjuji_get_template('scaffold.controller.controller', $this->baseTemplateType);
        }
        dd($templateData);
        /**
         * tian add end
         */

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nController created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    /**
     * [generateDataTable 【产生datatables】]
     * @return [type] [description]
     */
    private function generateDataTable()
    {
        $templateData = yunjuji_get_template('scaffold.datatable', $this->baseTemplateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $headerFieldTemplate = yunjuji_get_template('scaffold.views.datatable_column', $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }
            $headerFields[] = $fieldTemplate = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );
        }

        $path = $this->commandData->config->pathDataTables;

        $fileName = $this->commandData->modelName.'DataTable.php';

        $fields = implode(','.infy_nl_tab(1, 3), $headerFields);

        $templateData = str_replace('$DATATABLE_COLUMNS$', $fields, $templateData);

        FileUtil::createFile($path, $fileName, $templateData);

        $this->commandData->commandComment("\nDataTable created: ");
        $this->commandData->commandInfo($fileName);
    }

    /**
     * [rollback 【回退】]
     * @return [type] [description]
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Controller file deleted: '.$this->fileName);
        }

        // tian comment
        // if ($this->commandData->getAddOn('datatables')) {
        //     if ($this->rollbackFile($this->commandData->config->pathDataTables, $this->commandData->modelName.'DataTable.php')) {
        //         $this->commandData->commandComment('DataTable file deleted: '.$this->fileName);
        //     }
        // }
    }
}
