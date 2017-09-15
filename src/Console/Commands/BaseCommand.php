<?php

/**
 * @Author: admin
 * @Date:   2017-09-15 14:17:11
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-15 17:38:09
 */

namespace Yunjuji\Generator\Console\Commands;

use InfyOm\Generator\Commands\BaseCommand as LaravelGeneratorBaseCommand;
use Yunjuji\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use Yunjuji\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Generators\TestTraitGenerator;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends LaravelGeneratorBaseCommand
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;

    /**
     * @var Composer
     */
    public $composer;

    /**
     * Create a new command instance.
     * 【创建一个新的命令实例】
     */
    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    public function handle()
    {
    	parent::handle();
        $this->commandData->modelName = $this->argument('model');
        // // 初始化通用数据【最后是调用GeneratorConfig里面的init】
        $this->commandData->initCommandData();
        // // 获得字段的函数
        $this->commandData->getFields();
    }

	/**
     * [generateCommonItems 【产生通用选项】]
     * @return [type] [description]
     */
    public function generateCommonItems()
    {	
    	// fromTable和migration
        if (!$this->commandData->getOption('fromTable') and !$this->isSkip('migration')) {
            $migrationGenerator = new MigrationGenerator($this->commandData);
            $migrationGenerator->generate();
        }

        // 模型
        if (!$this->isSkip('model')) {
            $modelGenerator = new ModelGenerator($this->commandData);
            $modelGenerator->generate();
        }

        // 仓库
        if (!$this->isSkip('repository')) {
            $repositoryGenerator = new RepositoryGenerator($this->commandData);
            $repositoryGenerator->generate();
        }
    }

    /**
     * [generateAPIItems 【产生API选项】]
     * @return [type] [description]
     */
    public function generateAPIItems()
    {	
    	// 请求
        if (!$this->isSkip('requests') and !$this->isSkip('api_requests')) {
            $requestGenerator = new APIRequestGenerator($this->commandData);
            $requestGenerator->generate();
        }

        // 控制器
        if (!$this->isSkip('controllers') and !$this->isSkip('api_controller')) {
            $controllerGenerator = new APIControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }

        // 路由
        if (!$this->isSkip('routes') and !$this->isSkip('api_routes')) {
            $routesGenerator = new APIRoutesGenerator($this->commandData);
            $routesGenerator->generate();
        }

        // 测试
        if (!$this->isSkip('tests') and $this->commandData->getAddOn('tests')) {
            $repositoryTestGenerator = new RepositoryTestGenerator($this->commandData);
            $repositoryTestGenerator->generate();

            $testTraitGenerator = new TestTraitGenerator($this->commandData);
            $testTraitGenerator->generate();

            $apiTestGenerator = new APITestGenerator($this->commandData);
            $apiTestGenerator->generate();
        }
    }

    /**
     * [generateScaffoldItems 【产生脚手架选项-注释了视图和菜单】]
     * @return [type] [description]
     */
    public function generateScaffoldItems()
    {
    	// 请求
        if (!$this->isSkip('requests') and !$this->isSkip('scaffold_requests')) {
            $requestGenerator = new RequestGenerator($this->commandData);
            $requestGenerator->generate();
        }

        // 控制器
        if (!$this->isSkip('controllers') and !$this->isSkip('scaffold_controller')) {
            $controllerGenerator = new ControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }

        // 视图
        // if (!$this->isSkip('views')) {
        //     $viewGenerator = new ViewGenerator($this->commandData);
        //     $viewGenerator->generate();
        // }

        // 路由
        if (!$this->isSkip('routes') and !$this->isSkip('scaffold_routes')) {
            $routeGenerator = new RoutesGenerator($this->commandData);
            $routeGenerator->generate();
        }

        // 菜单
        // if (!$this->isSkip('menu') and $this->commandData->config->getAddOn('menu.enabled')) {
        //     $menuGenerator = new MenuGenerator($this->commandData);
        //     $menuGenerator->generate();
        // }
    }

    /**
     * [performPostActions 是否需要运行migrate]
     * @param  boolean $runMigration [description]
     * @return [type]                [description]
     */
    public function performPostActions($runMigration = false)
    {
        if ($this->commandData->getOption('save')) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->commandData->config->forceMigrate) {
                $this->call('migrate');
            } elseif (!$this->commandData->getOption('fromTable') and !$this->isSkip('migration')) {
                if ($this->commandData->getOption('jsonFromGUI')) {
                    $this->call('migrate');
                } elseif ($this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                    $this->call('migrate');
                }
            }
        }
        if (!$this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    /**
     * [isSkip 是否跳过某一项生成, 例如: 跳过request]
     * @param  [type]  $skip [description]
     * @return boolean       [description]
     */
    public function isSkip($skip)
    {
        if ($this->commandData->getOption('skip')) {
            return in_array($skip, (array) $this->commandData->getOption('skip'));
        }

        return false;
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    private function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->commandData->fields as $field) {
            $fileFields[] = [
                'name'        => $field->name,
                'dbType'      => $field->dbInput,
                'htmlType'    => $field->htmlInput,
                'validations' => $field->validations,
                'searchable'  => $field->isSearchable,
                'fillable'    => $field->isFillable,
                'primary'     => $field->isPrimary,
                'inForm'      => $field->inForm,
                'inIndex'     => $field->inIndex,
            ];
        }

        foreach ($this->commandData->relations as $relation) {
            $fileFields[] = [
                'type'     => 'relation',
                'relation' => $relation->type.','.implode(',', $relation->inputs),
            ];
        }

        $path = config('infyom.laravel_generator.path.schema_files', base_path('resources/model_schemas/'));

        $fileName = $this->commandData->modelName.'.json';

        if (file_exists($path.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        FileUtil::createFile($path, $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nSchema File saved: ");
        $this->commandData->commandInfo($fileName);
    }

    /**
     * @param $fileName
     * @param string $prompt
     *
     * @return bool
     */
    protected function confirmOverwrite($fileName, $prompt = '')
    {
        $prompt = (empty($prompt))
            ? $fileName.' already exists. Do you want to overwrite it? [y|N]'
            : $prompt;

        return $this->confirm($prompt, false);
    }

    /**
     * Get the console command options.
     * 【获得控制台命令选项】
     *
     * @return array
     */
    public function getOptions()
    {
        return [
        	/**
        	 * tian add start
        	 */
            // 是否需要 `rbac` 鉴权的参数
            ['rbac', null, InputOption::VALUE_REQUIRED, 'judging if you need it or not "rbac". (option:null, true)'],
            // form表单模式, 比如 `laravel-admin`, `larvel-backpack`等
            ['formMode', null, InputOption::VALUE_REQUIRED, 'Form Mode(options:empty, laravel-backpack, laravel-admin)'],
            // 过滤文件【过滤区域的字段信息】
            ['filterFieldsFile', null, InputOption::VALUE_REQUIRED, 'Filter Fields input as json file'],
        	/**
        	 * tian add end
        	 */
        	
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['jsonFromGUI', null, InputOption::VALUE_REQUIRED, 'Direct Json string while using GUI interface'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,api_controller,scaffold_controller,repository,requests,api_requests,scaffold_requests,routes,api_routes,scaffold_routes,views,tests,menu,dump-autoload)'],
            ['datatables', null, InputOption::VALUE_REQUIRED, 'Override datatables settings'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Specify only the views you want generated: index,create,edit,show'],
            ['relations', null, InputOption::VALUE_NONE, 'Specify if you want to pass relationships for fields'],
        ];
    }

    /**
     * Get the console command arguments.
     * 【获得控制台命令参数】
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
        ];
    }
}
