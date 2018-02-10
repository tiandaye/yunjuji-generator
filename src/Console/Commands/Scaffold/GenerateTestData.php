<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:44:54
 * @Last Modified by:   admin
 * @Last Modified time: 2017-10-12 23:06:17
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Illuminate\Console\Command;
use Yunjuji\Generator\Util;


class GenerateTestData extends Command
{
    use Util;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:generateTestData {path} {generatePath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The php command
     *
     * @var string
     */
    protected $phpCommand = 'php';
    // 模板文件的存放目录
    private $templatePath;
    // seeder类的路径
    private $seederPath;
    // factory类的路径
    private $factoryPath;
    // seed.json文件所在的实际目录
    private $jsonPath;
    // 项目路径
    private $proRootPath;
    // 填充记录数默认值
    const DEFAULT_MODEL_NUM = 100;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        if (config('yunjuji.generate.command.php_command')) {
            $this->phpCommand = config('yunjuji.generate.command.php_command');
        }
        if (config('custom.base.command.php_command')) {
            $this->phpCommand = config('custom.base.command.php_command');
        }
        // 初始化模板的目录
        $this->templatePath = str_replace('\\', '/', __DIR__ . '/../../../../templates/extension/testdata/');
        // var_dump($this->phpCommand);exit; // 打印php
        parent::__construct();
    }

    /**
     * 调用此方法生成视图文件, 以及admin/extensions/tool下的工具文件
     */
    public function handle()
    {
        // 获取命令行中json文件存放的根目录
        $this->jsonPath = str_replace('\\', '/', $this->argument('path'));
        // 获取命令行中项目文件的根目录
        $this->proRootPath = str_replace('\\', '/', $this->argument('generatePath')) . '/';
        // 获取seeder类文件和factory类文件存放目录
        $this->seederPath  = $this->proRootPath . 'database/seeds/';
        $this->factoryPath = $this->proRootPath . 'database/factories/';
        $callCode          = [];
        $this->parseJson($this->jsonPath, $callCode);
        $this->generateCallSeeder(implode('', $callCode));
    }

    /**
     * 解析seed.json文件
     * @param $path string seed.json文件的实际存放的物理路径
     * @param $callCode array 存储$callCode, 用于替换DatabaseSeeder.php中的调用模板代码
     */
    public function parseJson($path, &$callCode)
    {
        // 读取seed.json文件信息
        $seedFile = $path . "/seed.json";
        if (!is_file($seedFile)) {
            dd($seedFile . ' file not exist!');
        }
        $seed = file_get_contents($seedFile);
        if (empty($seed)) {
            dd($seedFile . ' file is empty!');
        }
        $seed = json_decode($seed);
        if (json_last_error() !== JSON_ERROR_NONE) {
            dd($seedFile . " json parse error, the reason: " . json_last_error_msg());
        }

        // 读取model.json文件
        $modelFile = $path . "/model.json";
        if (!is_file($modelFile)) {
            dd($modelFile . ' file not exist!');
        }
        $model = file_get_contents($modelFile);
        if (empty($seed)) {
            dd($modelFile . ' file is empty!');
        }
        $model = json_decode($model);
        if (json_last_error() !== JSON_ERROR_NONE) {
            dd($modelFile . " json parse error, the reason: " . json_last_error_msg());
        }
        $mapping = $this->generateMapping($model, $seed);
        // 生成seeder类
        $this->generateSeeder($model->model_name, $mapping);
        $callCodeTemplate = $this->generateCallCode($model->model_name);
        array_unshift($callCode, $callCodeTemplate);
        if ($seed->relation === 0) {
            // 没有关联关系
            $this->generateFactory($seed->fields, $model->model_name, $model->prefix_name);
        } elseif ($seed->relation === 1) {
            // 读取fields.json文件
            $fieldsFile = $path . '/fields.json';
            if (!is_file($fieldsFile)) {
                dd($fieldsFile . ' file not exist!');
            }
            $fields = file_get_contents($fieldsFile);
            if (empty($seed)) {
                dd($fieldsFile . ' file is empty!');
            }
            $fields = json_decode($fields);
            if (json_last_error() !== JSON_ERROR_NONE) {
                dd($fieldsFile . " json parse error, the reason: " . json_last_error_msg());
            }
            $fieldDatas = $seed->fields;

            // 读取命名空间文件namespace_model_mapping.json
            $namespaceModelMappingFile = $path . '/namespace_model_mapping.json';
            if (!is_file($namespaceModelMappingFile)) {
                dd($namespaceModelMappingFile . ' file not exist!');
            }
            $modelNamespaces = file_get_contents($namespaceModelMappingFile);
            if (empty($seed)) {
                dd($namespaceModelMappingFile . ' file is empty!');
            }
            $modelNamespaces = json_decode($modelNamespaces, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                dd($namespaceModelMappingFile . " json parse error, the reason: " . json_last_error_msg());
            }

            // 遍历fields
            $getIds = '';
            foreach ($fields as $field) {
                // 不是关联字段信息, 跳过
                if (empty($field->relation)) {
                    continue;
                }
                $relation = explode(',', $field->relation);
                // 判断是1t1, 1tm,  还是mtm
                if ($relation[0] === '1t1' || $relation[0] === '1tm') {
                    // 判断关联中另一张表是否有数据
                    $modelNameOtherAllPath = $modelNamespaces[$relation[1]];
                    // $this->isEmpty($modelName::count(), "please fill {$relation[1]} first!");
                    // 拼装数据
                    $fieldData        = new \stdClass();
                    $fieldData->name  = $relation[3];
                    $fieldData->type  = 'relation';
                    $fieldData->value = "\${$relation[3]}s";
                    array_push($fieldDatas, $fieldData);
                    $getIds .= $this->generateGetIds($relation[3], $modelNameOtherAllPath);
                } elseif ($relation[0] === 'mtm') {
                    // 判断关联中另一张表是否有数据
                    $modelNameOtherAllPath = $modelNamespaces[$relation[1]];
                    // $this->isEmpty($modelName1::count(), "please fill {$relation[1]} first!");
                    // 生成中间表的seed文件
                    $mapping = [
                        '$ASOC_MODEL_NAME$'                       => $this->upperCamelCase($relation[2]),
                        '$NAMESPACE$'                             => $model->prefix_name,
                        '$UPPER_CAMEL_MODEL_NAME_SELF$'           => $model->model_name,
                        '$UPPER_CAMEL_MODEL_NAME_OTHER_ALL_PATH$' => $modelNameOtherAllPath,
                        '$ASOC_FIELD_SELF$'                       => $relation[3],
                        '$ASOC_FIELD_OTHER$'                      => $relation[4],
                    ];
                    // 生成seeder类文件
                    $this->generateMtmSeeder($this->upperCamelCase($relation[2]), $mapping);
                    // 生成中间表的调用
                    $callCodeTemplate = $this->generateCallCode($this->upperCamelCase($relation[2]));
                    array_push($callCode, $callCodeTemplate);
                } else {
                    dd('not support this relationship: ' . $relation[0]);
                }
            }
            $this->generateFactory($fieldDatas, $model->model_name, $model->prefix_name, $getIds);
        } else {
            dd('seed.json relation field only be 0 or 1!');
        }

    }


    /**
     * @param $fields array 字段数组
     * @param $modelName string 模型名
     * @param $prefixName string 模型命名空间名
     * @param string $getIds
     */
    public function generateFactory($fields, $modelName, $prefixName, $getIds = '')
    {
        $keyValue = '';
        foreach ($fields as $field) {
            switch ($field->type) {
                case 'name':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->name');
                    break;
                case 'email':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->email');
                    break;
                case 'username':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->username');
                    break;
                case 'password':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "bcrypt('{$field->value}')");
                    break;
                case 'phone':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->phoneNumber');
                    break;
                case 'department':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->randomElement([\'人力资源部\',\'财务部\',\'销售部\',\'研发部\',\'秘书室\',\'采购部\'])');
                    break;
                case 'idCard':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, 'mt_rand(100000000, 999999999) . mt_rand(100000000, 999999999)');
                    break;
                case 'string':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "{$field->value}");
                    break;
                case 'date':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, 'date(\'Y-m-d\')');
                    break;
                case 'time':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, 'date(\'H:i:s\')');
                    break;
                case 'datetime':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, 'date(\'Y-m-d H:i:s\')');
                    break;
                case 'int':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, $field->value);
                    break;
                case 'randomInt':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "mt_rand({$field->value})");
                    break;
                case 'price':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->randomFloat(2, {$field->value})");
                    break;
                case 'randomFloat':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->randomFloat({$field->value})");
                    break;
                case 'randomElement':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->randomElement({$field->value})");
                    break;
                case 'randomElements':
                    $num      = array_pop($field->value);
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "json_encode(\$faker->randomElements({$field->value}, $num), JSON_UNESCAPED_UNICODE)");
                    break;
                case 'latitude':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->latitude');
                    break;
                case 'longitude':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->longitude');
                    break;
                case 'address':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->address');
                    break;
                case 'gender':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->randomElement({$field->value})");
                    break;
                case 'url':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->url');
                    break;
                case 'ip':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, '$faker->ipv4');
                    break;
                case 'realText':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->realText({$field->value}, 2)");
                    break;
                case 'relation':
                    $keyValue .= $this->generateFactoryKeyValue($field->name, "\$faker->randomElement({$field->value})");
                    break;
            }
        }
        $keyValue        .= $this->generateFactoryKeyValue('created_at', 'date(\'Y-m-d H:i:s\')');
        $factoryTemplate = file_get_contents("{$this->templatePath}factory.stub");
        $factoryTemplate = str_replace('$NAMESPACE$', $prefixName, $factoryTemplate);
        $factoryTemplate = str_replace('$UPPER_CAMEL_MODEL_NAME_SELF$', $modelName, $factoryTemplate);
        $factoryTemplate = str_replace('$GET_IDS$', $getIds, $factoryTemplate);
        $factoryTemplate = str_replace('$FACTORY_KEY_VALUES$', $keyValue, $factoryTemplate);
        // 如果目录不存在, 创建该目录
        $this->mkdir($this->factoryPath);
        file_put_contents("{$this->factoryPath}{$modelName}Factory.php", $factoryTemplate);
    }


    /**
     * 生成seeder类文件
     * @param $modelName string 模型名
     * @param $mapping
     */
    public function generateSeeder($modelName, $mapping)
    {
        // 获取模板字符串内容
        $seeder = file_get_contents("{$this->templatePath}seeder.stub");
        // 批量替换模板字符串
        $seeder = str_replace(array_keys($mapping), array_values($mapping), $seeder);
        // 如果目录不存在, 创建给定的目录
        $this->mkdir($this->seederPath);
        // 生成文件
        file_put_contents("{$this->seederPath}{$modelName}TableSeeder.php", $seeder);
    }


    /**
     * @param $callCode string seeder调用类中需要被替换的代码的字符串形式
     */
    public function generateCallSeeder($callCode)
    {
        // 生成调用类
        $databaseSeeder = file_get_contents("{$this->templatePath}database_seeder.stub");
        $databaseSeeder = str_replace('$CALL_CODE$', $callCode, $databaseSeeder);
        $this->mkdir($this->seederPath);
        file_put_contents("{$this->seederPath}DatabaseSeeder.php", $databaseSeeder);
    }

    /**
     * @param $modelName
     * @return string 返回替换之后的字符串
     */
    public function generateCallCode($modelName)
    {
        // 获取模板字符串内容
        $callCode = file_get_contents("{$this->templatePath}database_seeder_call_code.stub");
        // 批量替换模板字符串
        return str_replace('$UPPER_CAMEL_MODEL_NAME_SELF$', $modelName, $callCode);
    }

    /**
     * @param $key
     * @param $value
     * @return bool|mixed|string
     */
    public function generateFactoryKeyValue($key, $value)
    {
        // 获取模板字符串内容
        $callCode = file_get_contents("{$this->templatePath}factory_key_value.stub");
        // 批量替换模板字符串
        $callCode = str_replace('$FACTORY_KEY$', $key, $callCode);
        $callCode = str_replace('$FACTORY_VALUE$', $value, $callCode);
        return $callCode;
    }

    /**
     * @param $id
     * @param $modelNameOtherAllPath
     * @return bool|mixed|string
     */
    public function generateGetIds($id, $modelNameOtherAllPath)
    {
        // 获取模板字符串内容
        $getIdsTemplate = file_get_contents("{$this->templatePath}factory_get_id.stub");
        // 批量替换模板字符串
        $getIdsTemplate = str_replace('$ID$', $id, $getIdsTemplate);
        $getIdsTemplate = str_replace('$UPPER_CAMEL_MODEL_NAME_OTHER_ALL_PATH$', $modelNameOtherAllPath, $getIdsTemplate);
        return $getIdsTemplate;
    }

    /**
     * @param $modelName
     * @param $mapping
     */
    public function generateMtmSeeder($modelName, $mapping)
    {
        // 获取模板信息
        $seederCodeTemplate = file_get_contents("{$this->templatePath}seeder2.stub");
        // 批量替换模板字符串中的变量
        $seederCodeTemplate = $this->strReplaces($seederCodeTemplate, $mapping);
        // 如果目录不存在创建目录
        $this->mkdir($this->seederPath);
        file_put_contents("{$this->seederPath}{$modelName}TableSeeder.php", $seederCodeTemplate);
    }

    /**
     * @param $model
     * @param $seed
     * @return array
     */
    public function generateMapping($model, $seed)
    {
        return [
            '$UPPER_CAMEL_MODEL_NAME_SELF$' => $model->model_name,
            '$NAMESPACE$'                   => $model->prefix_name,
            '$MODEL_NUM$'                   => empty($seed->model_num) ? self::DEFAULT_MODEL_NUM : $seed->model_num,
        ];
    }

}
