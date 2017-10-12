<?php

namespace Yunjuji\Generator\Common;

use Exception;
use Illuminate\Console\Command;
use InfyOm\Generator\Common\CommandData as LaravelGeneratorCommandData;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;
use InfyOm\Generator\Utils\FileUtil;

class CommandData extends LaravelGeneratorCommandData
{
    public static $COMMAND_TYPE_API          = 'api';
    public static $COMMAND_TYPE_SCAFFOLD     = 'scaffold';
    public static $COMMAND_TYPE_API_SCAFFOLD = 'api_scaffold';
    public static $COMMAND_TYPE_VUEJS        = 'vuejs';

    /** @var string */
    public $modelName;
    public $commandType;

    /** @var GeneratorConfig */
    public $config;

    /** @var GeneratorField[] */
    public $fields = [];

    /** @var GeneratorFieldRelation[] */
    public $relations = [];

    /** @var Command */
    public $commandObj;

    /** @var array */
    public $dynamicVars       = [];
    public $fieldNamesMapping = [];

    /** @var CommandData */
    protected static $instance = null;

    /**
     * tian add start
     */
    // 各个模型的命名空间 @var $namespaceModelMapping[]
    public $namespaceModelMapping = [];
    // 保存有关联关系的字段 @var $hasRelationFields[]
    public $hasRelationFields = [];
    // 过滤字段 @var $filterFields[]
    public $filterFields = [];
    // 过滤字段的动态变量
    public $filterDynamicVars = [];
    // 过滤字段的映射变量
    public $filterFieldNamesMapping = [];
    // 有m2m关系的字段 @var $hasM2mRelationFields[]
    public $hasM2mRelationFields = [];
    // 有hmt关系的字段 @var $hasHmtRelationFields[]
    public $hasHmtRelationFields = [];
    // 有mhm关系的字段 @var $hasMhmRelationFields[]
    public $hasMhmRelationFields = [];
    // mhtm关系的字段 @var $hasMhtmRelationFields[]
    public $hasMhtmRelationFields = [];

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
    /**
     * tian add end
     */

    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param Command $commandObj
     * @param string  $commandType
     *
     * @return CommandData
     */
    public function __construct(Command $commandObj, $commandType)
    {
        // 命令对象和命令类型
        $this->commandObj  = $commandObj;
        $this->commandType = $commandType;

        // 字段名映射
        $this->fieldNamesMapping = [
            '$FIELD_NAME_TITLE$' => 'fieldTitle',
            '$FIELD_NAME$'       => 'name',
            // '$FIELD_DATA$'        => 'data'
        ];

        /**
         * tian add start
         */
        // 过滤字段名映射
        $this->filterFieldNamesMapping = [
            // 过滤字段中的模板替换直接复用了字段中的模板替换, 因为过滤字段中没有data属性, 所以需要覆盖下
            '$FIELD_DATA$' => 'name',
            // '$FIELD_NAME_TITLE$' => 'fieldTitle',
            // '$FIELD_NAME$'       => 'name',
            // '$TABLE_NAME$'       => 'table_name',
            '$OPERATOR$'   => 'operator',
        ];
        /**
         * tian add end
         */

        $this->config = new GeneratorConfig();
    }

    /**
     * [commandError 【命令错误】]
     * @param  [type] $error [description]
     * @return [type]        [description]
     */
    public function commandError($error)
    {
        $this->commandObj->error($error);
    }

    /**
     * [commandComment 【命令注释】]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public function commandComment($message)
    {
        $this->commandObj->comment($message);
    }

    /**
     * [commandWarn 【命令警告]
     * @param  [type] $warning [description]
     * @return [type]          [description]
     */
    public function commandWarn($warning)
    {
        $this->commandObj->warn($warning);
    }

    /**
     * [commandInfo 【命令信息】]
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public function commandInfo($message)
    {
        $this->commandObj->info($message);
    }

    /**
     * [initCommandData 【初始化通用数据】【调用 `GeneratorConfig` 模型的的 `init`函数】]
     * @return [type] [description]
     */
    public function initCommandData()
    {
        $this->config->init($this);
    }

    /**
     * [getOption 【获得配置文件里面的选项】]
     * @param  [type] $option [description]
     * @return [type]         [description]
     */
    public function getOption($option)
    {
        return $this->config->getOption($option);
    }

    /**
     * [getAddOn description]
     * @param  [type] $option [description]
     * @return [type]         [description]
     */
    public function getAddOn($option)
    {
        return $this->config->getAddOn($option);
    }

    /**
     * [setOption 【设置配置文件里面的选项】]
     * @param [type] $option [description]
     * @param [type] $value  [description]
     */
    public function setOption($option, $value)
    {
        $this->config->setOption($option, $value);
    }

    /**
     * [addDynamicVariable 【添加动态变量】]
     * @param [type] $name [description]
     * @param [type] $val  [description]
     */
    public function addDynamicVariable($name, $val)
    {
        $this->dynamicVars[$name] = $val;
    }

    /**
     * [getFields 【获得字段】]
     * @return [type] [description]
     */
    public function getFields()
    {
        $this->fields = [];

        if ($this->getOption('fieldsFile') or $this->getOption('jsonFromGUI')) {
            // 获得输入表单文件或者json
            $this->getInputFromFileOrJson();
        } elseif ($this->getOption('fromTable')) {
            // 获得输入表单表格
            $this->getInputFromTable();
        } else {
            // 获得输入表单控制台
            $this->getInputFromConsole();
        }
    }

    /**
     * [getInputFromConsole 【获得输入form控制台的字段】]
     * @return [type] [description]
     */
    private function getInputFromConsole()
    {
        $this->commandInfo('Specify fields for the model (skip id & timestamp fields, we will add it automatically)');
        $this->commandInfo('Read docs carefully to specify field inputs)');
        $this->commandInfo('Enter "exit" to finish');

        $this->addPrimaryKey();

        while (true) {
            $fieldInputStr = $this->commandObj->ask('Field: (name db_type html_type options)', '');

            if (empty($fieldInputStr) || $fieldInputStr == false || $fieldInputStr == 'exit') {
                break;
            }

            if (!GeneratorFieldsInputUtil::validateFieldInput($fieldInputStr)) {
                $this->commandError('Invalid Input. Try again');
                continue;
            }

            $validations = $this->commandObj->ask('Enter validations: ', false);
            $validations = ($validations == false) ? '' : $validations;

            if ($this->getOption('relations')) {
                $relation = $this->commandObj->ask('Enter relationship (Leave Black to skip):', false);
            } else {
                $relation = '';
            }

            $this->fields[] = GeneratorFieldsInputUtil::processFieldInput(
                $fieldInputStr,
                $validations
            );

            if (!empty($relation)) {
                $this->relations[] = GeneratorFieldRelation::parseRelation($relation);
            }
        }

        $this->addTimestamps();
    }

    /**
     * [addPrimaryKey 【添加主键】]
     */
    private function addPrimaryKey()
    {
        $primaryKey = new GeneratorField();
        if ($this->getOption('primary')) {
            $primaryKey->name = $this->getOption('primary');
        } else {
            $primaryKey->name = 'id';
        }
        $primaryKey->parseDBType('increments');
        $primaryKey->parseOptions('s,f,p,if,ii');

        $this->fields[] = $primaryKey;
    }

    /**
     * [addTimestamps 【添加时间戳】]
     */
    private function addTimestamps()
    {
        $createdAt       = new GeneratorField();
        $createdAt->name = 'created_at';
        $createdAt->parseDBType('timestamp');
        $createdAt->parseOptions('s,f,if,ii');
        $this->fields[] = $createdAt;

        $updatedAt       = new GeneratorField();
        $updatedAt->name = 'updated_at';
        $updatedAt->parseDBType('timestamp');
        $updatedAt->parseOptions('s,f,if,ii');
        $this->fields[] = $updatedAt;
    }

    /**
     * [getInputFromFileOrJson 【获得字段列表, 从输入表单文件或者json】]
     * @return [type] [description]
     */
    private function getInputFromFileOrJson()
    {
        /**
         * tian add start
         */
        // 选择的模板
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->getOption('formMode')) {
            $this->formMode       = $this->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        /**
         * tian add end
         */

        // fieldsFile option will get high priority than json option if both options are passed
        try {
            if ($this->getOption('fieldsFile')) {
                $fieldsFileValue = $this->getOption('fieldsFile');
                if (file_exists($fieldsFileValue)) {
                    $filePath = $fieldsFileValue;
                } elseif (file_exists(base_path($fieldsFileValue))) {
                    $filePath = base_path($fieldsFileValue);
                } else {
                    $schemaFileDirector = config('infyom.laravel_generator.path.schema_files');
                    $filePath           = $schemaFileDirector . $fieldsFileValue;
                }

                if (!file_exists($filePath)) {
                    $this->commandError('Fields file not found');
                    exit;
                }

                $fileContents = file_get_contents($filePath);
                $jsonData     = json_decode($fileContents, true);
                $this->fields = [];
                foreach ($jsonData as $field) {
                    if (isset($field['type']) && $field['relation']) {
                        /**
                         * tian comment start
                         */
                        // $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        /**
                         * tian comment end
                         */

                        /**
                         * tian add
                         */
                        $tempInputs = explode(',', $field['relation']);
                        // 如果是m2m多对多/hmt远层一对多/多态关联/多态多对多关联-则需要将中间表的表名当做字段存到字段里面
                        if ($tempInputs[0] == 'mtm') {
                            // 用 `模型名` 当 `字段名`
                            $relationName = camel_case($tempInputs[1]);
                            if (substr($relationName, -1) != 's') {
                                $relationName .= 's';
                            }
                            $field['name'] = $relationName;
                            // 只是为了防止报错才加这行【因为我现在是试图将一个关系当做一个字段】
                            $field['dbType'] = 'integer';
                            $tempField       = GeneratorField::parseFieldFromFile($field);
                            // 添加到有m2m关联关系的字段数组里面
                            $this->hasM2mRelationFields[] = $tempField;
                            $tempRelation                 = GeneratorFieldRelation::parseRelation($field['relation']);
                            $this->relations[]            = $tempRelation;
                            // 给hasRelationFields进行赋值
                            $this->hasRelationFields[$tempField->name] = array('name' => $tempField->name, 'inForm' => $tempField->inForm, 'inIndex' => $tempField->inIndex, 'label' => $tempField->label, 'title' => $tempField->title, 'htmlType' => $tempField->htmlType, 'htmlValues' => $tempField->htmlValues, 'displayField' => $tempField->displayField, 'type' => $tempRelation->type, 'inputs' => $tempRelation->inputs, 'number' => count($this->relations) - 1);

                            // `多对多关联` 产生 `中间表` 的 `migrate`
                            $templateData = get_template('migration', 'laravel-generator');
                            $templateData = yunjuji_get_template($this->formModePrefix . 'migration.middle_table', $this->baseTemplateType);
                            $migrateBatch = '';
                            if ($this->getOption('migrateBatch')) {
                                $migrateBatch = $this->getOption('migrateBatch');
                            }
                            $fields = [];
                            // $fields[] = '$table->increments(\'id\', 10)->unsigned();';
                            // $fields[] = '$table->integer(\'' . $tempInputs[3] . '\', 10)->unsigned();';
                            // $fields[] = '$table->integer(\'' . $tempInputs[4] . '\', 10)->unsigned();';
                            $fields[] = '$table->increments(\'id\');';
                            $fields[] = '$table->integer(\'' . $tempInputs[3] . '\');';
                            $fields[] = '$table->integer(\'' . $tempInputs[4] . '\');';
                            // $fields[] = '$table->timestamps();';
                            $tableName = $tempInputs[2];
                            $templateData = str_replace('$MODEL_NAME_PLURAL$', studly_case($tableName).$migrateBatch, $templateData);
                            $templateData = str_replace('$TABLE_NAME$', $tableName, $templateData);
                            $templateData = str_replace('$FIELDS$', implode(yunjuji_nl_tab(1, 4), $fields), $templateData);
                            $fileName = date('Y_m_d_His').'_'.'create_'.$tableName.$migrateBatch.'_table.php';
                            $path = config('infyom.laravel_generator.path.migration', base_path('database/migrations/'));
                            if ($generatePath = $this->getOption('generatePath')) {
                                $path = $generatePath . '/' . 'database/migrations/';
                            }
                            FileUtil::createFile($path, $fileName, $templateData);
                        } else if ($tempInputs[0] == 'hmt') {
                            // 用中间表表名当字段名
                            $field['name'] = $tempInputs[1];
                            // 只是为了防止报错才加这行【因为我现在是试图将一个关系当做一个字段】
                            $field['dbType'] = 'integer';
                            $tempField       = GeneratorField::parseFieldFromFile($field);
                            // 添加到有hmt远层一对多关联关系的字段数组里面
                            $this->hasHmtRelationFields[] = $tempField;
                            $tempRelation                 = GeneratorFieldRelation::parseRelation($field['relation']);
                            $this->relations[]            = $tempRelation;
                            // 给hasRelationFields进行赋值
                            $this->hasRelationFields[$tempField->name] = array('name' => $tempField->name, 'inForm' => $tempField->inForm, 'inIndex' => $tempField->inIndex, 'label' => $tempField->label, 'title' => $tempField->title, 'htmlType' => $tempField->htmlType, 'htmlValues' => $tempField->htmlValues, 'displayField' => $tempField->displayField, 'type' => $tempRelation->type, 'inputs' => $tempRelation->inputs, 'number' => count($this->relations) - 1);
                        } else if ($tempInputs[0] == 'mhm') {
                            // 用中间表表名当字段名
                            $field['name'] = $tempInputs[1];
                            // 只是为了防止报错才加这行【因为我现在是试图将一个关系当做一个字段】
                            $field['dbType'] = 'integer';
                            $tempField       = GeneratorField::parseFieldFromFile($field);
                            // 添加到有mhm多态morphMany关联关系的字段数组里面
                            $this->hasMhmRelationFields[] = $tempField;
                            $tempRelation                 = GeneratorFieldRelation::parseRelation($field['relation']);
                            $this->relations[]            = $tempRelation;
                            // 给hasRelationFields进行赋值
                            $this->hasRelationFields[$tempField->name] = array('name' => $tempField->name, 'inForm' => $tempField->inForm, 'inIndex' => $tempField->inIndex, 'label' => $tempField->label, 'title' => $tempField->title, 'htmlType' => $tempField->htmlType, 'htmlValues' => $tempField->htmlValues, 'displayField' => $tempField->displayField, 'type' => $tempRelation->type, 'inputs' => $tempRelation->inputs, 'number' => count($this->relations) - 1);
                        } else if ($tempInputs[0] == 'mhtm') {
                            // 用中间表表名当字段名
                            $field['name'] = $tempInputs[1];
                            // 只是为了防止报错才加这行【因为我现在是试图将一个关系当做一个字段】
                            $field['dbType'] = 'integer';
                            $tempField       = GeneratorField::parseFieldFromFile($field);
                            // 添加到有mhtm多态多对多关联morphToMany关联关系的字段数组里面
                            $this->hasMhtmRelationFields[] = $tempField;
                            $tempRelation                  = GeneratorFieldRelation::parseRelation($field['relation']);
                            $this->relations[]             = $tempRelation;
                            // 给hasRelationFields进行赋值
                            $this->hasRelationFields[$tempField->name] = array('name' => $tempField->name, 'inForm' => $tempField->inForm, 'inIndex' => $tempField->inIndex, 'label' => $tempField->label, 'title' => $tempField->title, 'htmlType' => $tempField->htmlType, 'htmlValues' => $tempField->htmlValues, 'displayField' => $tempField->displayField, 'type' => $tempRelation->type, 'inputs' => $tempRelation->inputs, 'number' => count($this->relations) - 1);
                        } else {
                            $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        }
                        /**
                         * tian end
                         */
                    } else {
                        /**
                         * tian comment start
                         */
                        // $this->fields[] = GeneratorField::parseFieldFromFile($field);
                        // if (isset($field['relation'])) {
                        //     $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        // }
                        /**
                         * tian comment end
                         */

                        /**
                         * tian start
                         */
                        $tempField      = GeneratorField::parseFieldFromFile($field);
                        $this->fields[] = $tempField;
                        if (isset($field['relation'])) {
                            $tempRelation      = GeneratorFieldRelation::parseRelation($field['relation']);
                            $this->relations[] = $tempRelation;
                            // 给hasRelationFields进行赋值
                            $this->hasRelationFields[$tempField->name] = array('name' => $tempField->name, 'inForm' => $tempField->inForm, 'inIndex' => $tempField->inIndex, 'label' => $tempField->label, 'title' => $tempField->title, 'htmlType' => $tempField->htmlType, 'htmlValues' => $tempField->htmlValues, 'displayField' => $tempField->displayField, 'type' => $tempRelation->type, 'inputs' => $tempRelation->inputs, 'number' => count($this->relations) - 1);
                        }
                        /**
                         * tian end
                         */
                    }
                }
            } else {
                $fileContents = $this->getOption('jsonFromGUI');
                $jsonData     = json_decode($fileContents, true);
                foreach ($jsonData['fields'] as $field) {
                    if (isset($field['type']) && $field['relation']) {
                        $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                    } else {
                        $this->fields[] = GeneratorField::parseFieldFromFile($field);
                        if (isset($field['relation'])) {
                            $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }

    /**
     * [getInputFromTable 【获得输入表单表格】]
     * @return [type] [description]
     */
    private function getInputFromTable()
    {
        $tableName = $this->dynamicVars['$TABLE_NAME$'];

        $tableFieldsGenerator = new TableFieldsGenerator($tableName);
        $tableFieldsGenerator->prepareFieldsFromTable();
        $tableFieldsGenerator->prepareRelations();

        $this->fields    = $tableFieldsGenerator->fields;
        $this->relations = $tableFieldsGenerator->relations;
    }

    /**
     * tian custom function
     * [getFilterFields 【获得过滤字段】]
     * @return [type] [description]
     */
    public function getFilterFields()
    {
        $this->filterFields = [];

        if ($this->getOption('filterFieldsFile')) {
            $this->getInputFilterFromFileOrJson();
        }
        // if ($this->getOption('fieldsFile') or $this->getOption('jsonFromGUI')) {
        //     //【获得输入表单文件或者json】
        //     $this->getInputFromFileOrJson();

        // } elseif ($this->getOption('fromTable')) {
        //     //【获得输入表单表格】
        //     $this->getInputFromTable();
        // } else {
        //     //【获得输入表单控制台】
        //     $this->getInputFromConsole();
        // }
    }

    /**
     * tian custom function
     * [getNamespaceModelMapping 【获得过滤字段】]
     * @return [type] [description]
     */
    public function getNamespaceModelMapping()
    {
        $this->namespaceModelMapping = [];

        if ($this->getOption('namespaceModelMappingFile')) {
            $this->getNamespaceModelMappingFromFileOrJson();
        }
    }

    /**
     * tian custom function
     * [getInputFilterFromFileOrJson 【获得过滤字段列表】]
     * @return [type] [description]
     */
    private function getInputFilterFromFileOrJson()
    {
        // fieldsFile option will get high priority than json option if both options are passed
        try {
            if ($this->getOption('filterFieldsFile')) {
                // 文件名, 用户传入的选项
                $fieldsFileValue = $this->getOption('filterFieldsFile');
                if (file_exists($fieldsFileValue)) {
                    $filePath = $fieldsFileValue;
                } elseif (file_exists(base_path($fieldsFileValue))) {
                    $filePath = base_path($fieldsFileValue);
                } else {
                    $schemaFileDirector = config('infyom.laravel_generator.path.schema_files');
                    $filePath           = $schemaFileDirector . $fieldsFileValue;
                }

                if (!file_exists($filePath)) {
                    $this->commandError('filter Fields file not found');
                    exit;
                }

                $fileContents       = file_get_contents($filePath);
                $jsonData           = json_decode($fileContents, true);
                $this->filterFields = [];

                foreach ($jsonData as $filterField) {
                    // 调用自定义的函数, 构建过滤字段
                    $this->filterFields[] = GeneratorFilterField::parseFilterFieldFile($filterField);
                }
            } else {
            }
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }

    /**
     * tian custom function
     * [getNamespaceModelMappingFromFileOrJson 【获得模型映射关系】]
     * @return [type] [description]
     */
    private function getNamespaceModelMappingFromFileOrJson()
    {
        // fieldsFile option will get high priority than json option if both options are passed
        try {
            if ($this->getOption('namespaceModelMappingFile')) {
                //文件名，用户传入的选项
                $namespaceModelMappingFile = $this->getOption('namespaceModelMappingFile');
                if (file_exists($namespaceModelMappingFile)) {
                    $filePath = $namespaceModelMappingFile;
                } else if (file_exists(base_path($namespaceModelMappingFile))) {
                    $filePath = base_path($namespaceModelMappingFile);
                } else {
                    $schemaFileDirector = config('infyom.laravel_generator.path.schema_files');
                    $filePath           = $schemaFileDirector . $namespaceModelMappingFile;
                }

                if (!file_exists($filePath)) {
                    $this->commandError('namespaceModelMappingFile Fields file not found');
                    exit;
                }

                $fileContents                = file_get_contents($filePath);
                $jsonData                    = json_decode($fileContents, true);
                $this->namespaceModelMapping = $jsonData;
            } else {
            }
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }
}
