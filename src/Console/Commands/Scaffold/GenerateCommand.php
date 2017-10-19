<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:44:54
 * @Last Modified by:   admin
 * @Last Modified time: 2017-10-12 23:06:17
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Illuminate\Console\Command;

class GenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:generate {path} {generatePath?} {migrateBatch?}';

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

    /**
     * Create a new command instance.
     *
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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 获取要遍历的路径
        $path = $this->argument('path');
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        // 获取全部的要模型json文件
        $fullMigratePath = $this->getFilePath($path, 'fields.json');
        // 生成的路径
        $generatePath = $this->argument('generatePath');
        // `migrate` 的次数
        $migrateBatch =  $this->argument('migrateBatch');
        // 批量执行命令
        $result = $this->generateCommand($fullMigratePath, $path, $generatePath, $migrateBatch);
    }

    /**
     * [getFilePath 递归遍历文件]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function getFilePath($path = '', $fileName = "fields.json")
    {
        $migratePath = [];
        // opendir()返回一个目录句柄,失败返回false
        $current_dir = opendir($path);
        // readdir()返回打开目录句柄中的一个条目
        while (($file = readdir($current_dir)) !== false) {
            //构建子目录路径
            $sub_dir = $path . DIRECTORY_SEPARATOR . $file;
            $sub_dir = str_replace(DIRECTORY_SEPARATOR, '/', $sub_dir);
            // 将操作系统的gbk转为utf-8
            $sub_dir = iconv('gbk', 'utf-8', $sub_dir);
            if ($file == '.' || $file == '..') {
                continue;
                //如果是目录,进行递归
            } else if (is_dir($sub_dir)) {
                $migratePath = array_merge($migratePath, $this->getFilePath($sub_dir, $fileName));
            } else {
                // 如果文件名等于要找的文件
                if ($file == $fileName) {
                    $sub_dir       = str_replace(DIRECTORY_SEPARATOR, '/', $sub_dir);
                    $migratePath[] = $sub_dir;
                }
            }
        }
        // 关闭句柄，释放资源
        closedir($current_dir);
        return $migratePath;
    }

    /**
     * [getFilePath 循环执行artisan命令]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function generateCommand($fullMigratePath, $path, $generatePath = '', $migrateBatch = '')
    {
        if (is_array($fullMigratePath)) {
            // 命令执行结果
            $commandResult = [];
            $menus = [];
            foreach ($fullMigratePath as $pathKey => $pathValue) {
                $migrateFileDir = dirname($pathValue);
                // 查找此目录下的prefix.json
                $prefixJson = $migrateFileDir . '/model.json';
                // 获取描述json信息
                $describeJson = file_get_contents($prefixJson);
                // json解码
                $arrDescription = json_decode($describeJson, 1);
                // 模型名
                $modelName = $arrDescription['model_name'];
                // --prefix
                $prefixName     = $arrDescription['prefix_name'];
                if (DIRECTORY_SEPARATOR != '\\') {
                    $prefixName = str_replace("\\", "\\\\", $prefixName);
                }
                // 中文名
                $title = $modelName;
                if (isset($arrDescription['title'])) {
                    $title = $arrDescription['title'];
                }
                $artisanCommand = "{$this->phpCommand} artisan yunjuji:scaffold $modelName --fieldsFile=$pathValue --datatables=true --formMode=laravel-admin --prefix=$prefixName";
                // `migrate` 的次数
                if (!empty($migrateBatch)) {
                    $artisanCommand .= " --migrateBatch=$migrateBatch";
                }
                // 生成到指定的路径
                if (!empty($generatePath)) {
                    $artisanCommand .= " --generatePath=$generatePath";
                }
                // 如果存在 `过滤区域` json
                $filterFilePath = $migrateFileDir . '/filter.json';
                if (file_exists($filterFilePath)) {
                    $artisanCommand .= " --filterFieldsFile=$filterFilePath";
                }
                // 如果存在 `命名空间映射` json
                // $namespaceModelMappingFilePath = $path . '/namespace_model_mapping.json';
                $namespaceModelMappingFilePath = $migrateFileDir . '/namespace_model_mapping.json';
                if (file_exists($namespaceModelMappingFilePath)) {
                    $artisanCommand .= " --namespaceModelMappingFile=$namespaceModelMappingFilePath";
                }
                $commandResult[] = passthru("echo no|$artisanCommand", $result);

                // 生成菜单
                // $aRoutePrefix = explode('\\', $prefixName);
                // 生成菜单
                if (DIRECTORY_SEPARATOR != '\\') {
                    $aRoutePrefix = explode('\\\\', $prefixName);
                } else {
                    $aRoutePrefix = explode('\\', $prefixName);
                }

                $aRoutePrefix = array_map(function ($val) {
                    // 转下划线命名法
                    return snake_case($val);
                }, $aRoutePrefix);
                $url = str_replace('.', '/', implode('/', $aRoutePrefix)) . '/' . snake_case(str_plural($modelName));
                $menus[] = ['name' => $title, 'url' => $url];
            }
            // 生成的路由菜单
            file_put_contents($generatePath . '/menu.json', json_encode($menus));

            return $commandResult;
        }
    }

}
