<?php

namespace Yunjuji\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class RequestGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $createFileName;

    /** @var string */
    private $updateFileName;

    /**
     * tian add
     * [$requestFileName 请求文件路径]
     * @var string tian add
     */
    private $requestFileName;

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
        /**
         * tian comment start
         */
        // $this->commandData = $commandData;
        // $this->path = $commandData->config->pathRequest;
        // $this->createFileName = 'Create'.$this->commandData->modelName.'Request.php';
        // $this->updateFileName = 'Update'.$this->commandData->modelName.'Request.php';
        /**
         * tian comment end
         */

        /**
         * tian add start
         */
        $this->commandData      = $commandData;
        $this->path             = $commandData->config->pathRequest;
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        // 请求不以两个文件(create, update)的形式出现
        $this->requestFileName = $this->commandData->modelName . 'Request.php';
        /**
         * tian add end
         */
    }

    /**
     * [generate 产生请求文件]
     * @return [type] [description]
     */
    public function generate()
    {
        /**
         * tian comment start
         */
        // $this->generateCreateRequest();
        // $this->generateUpdateRequest();
        /**
         * tian comment end
         */

        /**
         * tian add start
         */
        $this->generateRequest();
        /**
         * tian add end
         */
    }

    /**
     * [generateCreateRequest 产生新建请求]
     * @return [type] [description]
     */
    private function generateCreateRequest()
    {
        $templateData = yunjuji_get_template('scaffold.request.create_request', 'laravel-generator');

        $templateData = yunjuji_fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->createFileName, $templateData);

        $this->commandData->commandComment("\nCreate Request created: ");
        $this->commandData->commandInfo($this->createFileName);
    }

    /**
     * [generateUpdateRequest 产生更新请求]
     * @return [type] [description]
     */
    private function generateUpdateRequest()
    {
        $templateData = yunjuji_get_template('scaffold.request.update_request', 'laravel-generator');

        $templateData = yunjuji_fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->commandData->commandComment("\nUpdate Request created: ");
        $this->commandData->commandInfo($this->updateFileName);
    }

    /**
     * [rollback 回退]
     * @return [type] [description]
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->commandData->commandComment('Create API Request file deleted: ' . $this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->commandData->commandComment('Update API Request file deleted: ' . $this->updateFileName);
        }

        if ($this->rollbackFile($this->path, $this->requestFileName)) {
            $this->commandData->commandComment('API Request file deleted: ' . $this->requestFileName);
        }
    }

    /**
     * tian add
     * [generateRequest 产生请求, 不分新建和修改]
     * @return [type] [description]
     */
    private function generateRequest()
    {
        $templateData = yunjuji_get_template($this->formModePrefix . 'scaffold.request.request', $this->baseTemplateType);

        $templateData = yunjuji_fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$CREATE_RULES$', implode(',' . infy_nl_tab(1, 2), $this->generateCreateRules()), $templateData);
        $templateData = str_replace('$UPDATE_RULES$', implode(',' . infy_nl_tab(1, 2), $this->generateUpdateRules()), $templateData);

        // `linux` 和 `win` 有区别
        if (DIRECTORY_SEPARATOR != '\\') {
            FileUtil::createFile(str_replace('\\', DIRECTORY_SEPARATOR, $this->path), $this->requestFileName, $templateData);
        } else {
            FileUtil::createFile($this->path, $this->requestFileName, $templateData);
        }

        $this->commandData->commandComment("\nRequest created: ");
        $this->commandData->commandInfo($this->requestFileName);
    }

    /**
     * tian add
     * [generateCreateRules 为了在请求中生成 `create rules` ]
     * @return [type] [description]
     */
    private function generateCreateRules()
    {
        $rules = [];

        foreach ($this->commandData->fields as $field) {
            if (!empty($field->validations)) {
                $rule    = yunjuji_tab(4 * 3) . "'" . $field->name . "' => '" . $field->validations . "'";
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * tian add
     * [generateUpdateRules 为了在请求中生成 `update rules` ]
     * @return [type] [description]
     */
    private function generateUpdateRules()
    {
        $rules = [];

        foreach ($this->commandData->fields as $field) {
            if (!empty($field->validations)) {
                $flag = false;
                $validations = explode('|', $field->validations);
                for ($i=0, $count = count($validations); $i < $count; $i++) {
                    if ($i == 0 ) {
                        $validations[$i] = '\'' . $validations[$i];
                    } else {
                        $validations[$i] = ' . \'|' . $validations[$i];
                    }
                    // 如果是 `唯一性` 校验, 更新的时候需要将自己排除了
                    if (str_contains($validations[$i], "unique:")) {
                        $flag = true;
                        $validations[$i] .= ',\' . $id . \'';
                    }
                    $validations[$i] = $validations[$i] . '\'';
                }
                if ($flag) {
                    $rule    = yunjuji_tab(4 * 3) . "'" . $field->name . "' => " . implode('', $validations);
                } else {
                    $rule    = yunjuji_tab(4 * 3) . "'" . $field->name . "' => '" . $field->validations . "'";
                }
                $rules[] = $rule;
            }
        }

        return $rules;
    }
}
