<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:51:43
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-21 18:17:00
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Yunjuji\Generator\Common\CommandData;
use Yunjuji\Generator\Console\Commands\BaseCommand;

class ScaffoldGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'yunjuji:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD views for given model';

    /**
     * Create a new command instance.
     * 【创建一个新的命令实例】
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        //  判断字段数是否大于3个
        if ($this->checkIsThereAnyDataToGenerate()) {
            // 产生通用的东西, 比如:migration, model, repository【调用BaseCommand】
            $this->generateCommonItems();

            // 产生脚手架有关的, 比如:requests, controllers, views, routes, menu【调用BaseCommand】
            $this->generateScaffoldItems();

            // 是否执行 `migrate`【调用BaseCommand】
            $this->performPostActionsWithMigration();
        } else {
            $this->commandData->commandInfo('There isn not input fields to generate.');
        }
    }

    /**
     * Get the console command options.
     * 【获得控制台命令选项】
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     * 【获得控制台命令参数】
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }

    /**
     * Check if there is anything to generate.
     * 【字段数大于3，才能自动产生】
     *
     * @return bool
     */
    protected function checkIsThereAnyDataToGenerate()
    {
        if (count($this->commandData->fields) > 3) {
            return true;
        }
    }
}
