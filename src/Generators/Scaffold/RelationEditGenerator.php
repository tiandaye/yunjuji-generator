<?php

namespace Yunjuji\Generator\Generators\Scaffold;

use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use Yunjuji\Generator\Common\CommandData;
use Yunjuji\Generator\Util;

class RelationEditGenerator extends BaseGenerator
{
    use Util;
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

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
        $this->commandData      = $commandData;
        $this->path             = $commandData->config->options['generatePath'];
        $this->templateType     = config('yunjuji.generator.templates.extend', 'core-templates');
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        $this->fileName = $this->commandData->modelName . 'Controller.php';
    }

    /**
     * [generate 【产生】]
     * @return [type] [description]
     */
    public function generate()
    {
        // 遍历关联关系字段
        foreach ($this->commandData->relations as $key => $relation) {
            $modleName  = $this->commandData->modelName;
            $prefixName = $this->commandData->dynamicVars['$ROUTE_PREFIX$'];
            $mapping = [
                '$RELATION_NAME$'        => $relation->inputs[0],
                '$RELATION_NAME_CAMEL$'  => camel_case($relation->inputs[0]),
                '$RELATION_NAME_SNAKE$'  => snake_case($relation->inputs[0]),
                '$MODEL_NAME_MIDDLE$'    => str_replace('_', '-', snake_case($modleName)),
                '$RELATION_NAME_MIDDLE$' => str_replace('_', '-', snake_case($modleName)),
                '$ROUTE_PREFIX_DASHED$'  => str_replace('/', '.', $prefixName),
                '$ROUTE_PREFIX_SLASH$'   => $prefixName,
            ];
            // 如果是hasMany或者manyToMany, 生成视图文件和对应的main.js代码
            if ($relation->type == '1tm' || $relation->type == 'mtm') {
                // 获取视图模板数据
                $hasManyViewTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.extension.relation-edit.has_many_batch_add', $this->baseTemplateType);
                $hasManyViewTemplateData = fill_template($this->commandData->dynamicVars, $hasManyViewTemplateData);
                $hasManyViewTemplateData = $this->strReplaces($hasManyViewTemplateData, $mapping);
                // 生成视图文件
                $hasManyViewFilePath = $this->path . '/resources/views/' . $prefixName . snake_case($modleName) . 's/';
                $hasManyViewFileName = snake_case($relation->inputs[0]) . '.blade.php';
                FileUtil::createFile(str_replace('\\', '/', $hasManyViewFilePath), $hasManyViewFileName, $hasManyViewTemplateData);
                // 生成对应的main.js文件函数
                $hasManyViewTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.extension.relation-edit.tab_item_content_js', $this->baseTemplateType);
                $hasManyViewTemplateData = fill_template($this->commandData->dynamicVars, $hasManyViewTemplateData);
                $hasManyViewTemplateData = $this->strReplaces($hasManyViewTemplateData, $mapping);
                $mainJsFile              = $this->path . '/public/packages/admin/custom/main.js';
                // 生成模板文件至指定目录
                // 如果目标目录不存在创建这个目录
                $this->mkdir(dirname($mainJsFile));
                file_put_contents($mainJsFile, $hasManyViewTemplateData, FILE_APPEND);
            }
            // 如果是belongToOne, 则生成批量归属的tool文件以及对应视图文件, 以及对应的main.js代码
            if ($relation->type == 'mt1') {
                $belongToOneTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.extension.relation-edit.belongs_to_one_bacth_oparate', $this->baseTemplateType);
                $belongToOneTemplateData = fill_template($this->commandData->dynamicVars, $belongToOneTemplateData);
                $belongToOneTemplateData = $this->strReplaces($belongToOneTemplateData, $mapping);
                // 生成视图文件
                $belongToOneViewFilePath = $this->path . '/resources/views/' . $prefixName . snake_case($modleName) . 's/';
                $belongToOneViewFileName = 'belong_to_' . snake_case($relation->inputs[0]) . '.blade.php';
                FileUtil::createFile(str_replace('\\', '/', $belongToOneViewFilePath), $belongToOneViewFileName, $belongToOneTemplateData);
                // 生成tool文件
                $belongToOneToolTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.extension.relation-edit.belongs_to_many_tool', $this->baseTemplateType);
                $belongToOneToolTemplateData = fill_template($this->commandData->dynamicVars, $belongToOneToolTemplateData);
                $belongToOneToolTemplateData = $this->strReplaces($belongToOneToolTemplateData, $mapping);
                $belongToOneToolFilePath     = $this->path . '/app/Admin/Extensions/Tools/' . $prefixName . snake_case($modleName) . '/';
                $belongToOneToolFileName     = 'BelongTo' . $relation->inputs[0] . '.php';
                FileUtil::createFile(str_replace('\\', '/', $belongToOneToolFilePath), $belongToOneToolFileName, $belongToOneToolTemplateData);
                // 生成自定义批量编辑工具文件中需要调用的js函数
                $belongToOneToolJsTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.extension.relation-edit.belongs_to_many_tool_js', $this->baseTemplateType);
                $belongToOneToolJsTemplateData = fill_template($this->commandData->dynamicVars, $belongToOneToolJsTemplateData);
                $belongToOneToolJsTemplateData = $this->strReplaces($belongToOneToolJsTemplateData, $mapping);
                $mainJsFile                    = $this->path . '/public/packages/admin/custom/main.js';
                // 生成模板文件至指定目录
                // 如果目标目录不存在创建这个目录
                $this->mkdir(dirname($mainJsFile));
                file_put_contents($mainJsFile, $belongToOneToolJsTemplateData, FILE_APPEND);
            }

            // 如果是多态morphTo关系,  在grid中添加批量编辑按钮暂时模板不能用
            // if ($relation->type == 'mht') {
            //     $relationName      = $relation->inputs[0];
            //     $batchEditTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.batch_edit', $this->baseTemplateType);
            // }
        }

    }
}
