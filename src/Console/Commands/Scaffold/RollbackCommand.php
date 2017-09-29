<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:53:53
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 09:54:50
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Illuminate\Console\Command;

class RollbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:rollback {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rollback';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $paths = $this->getFilePath($path, 'fields.json');
        // 批量回退
        $result = $this->rollback($paths);
    }

    /**
     * [getFilePath 递归遍历文件]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function getFilePath($path = '', $fileName = 'fields.json')
    {
        $fieldsPath = [];
        // opendir()返回一个目录句柄,失败返回false
        $currentDir = opendir($path);
        // readdir()返回打开目录句柄中的一个条目
        while (($file = readdir($currentDir)) !== false) {
            // 构建子目录路径
            $subDir = $path . DIRECTORY_SEPARATOR . $file;
            $subDir = str_replace(DIRECTORY_SEPARATOR, '/', $subDir);
            // 将操作系统的gbk转为utf-8
            $subDir = iconv('gbk', 'utf-8', $subDir);
            if ($file == '.' || $file == '..') {
                continue;
                // 如果是目录,进行递归
            } else if (is_dir($subDir)) {
                $fieldsPath = array_merge($fieldsPath, $this->getFilePath($subDir, $fileName));
            } else {
                // 如果文件名等于要找的文件
                if ($file == $fileName) {
                    $subDir       = str_replace(DIRECTORY_SEPARATOR, '/', $subDir);
                    $fieldsPath[] = $subDir;
                }
            }
        }
        // 关闭句柄，释放资源
        closedir($currentDir);
        return $fieldsPath;
    }

    /**
     * [rollback 批量回滚]
     * @param  [type] $paths [description]
     * @return [type]        [description]
     */
    public function rollback($paths)
    {
        if (is_array($paths)) {
            // 命令执行结果
            $commandResult = [];
            foreach ($paths as $pathKey => $pathValue) {
                $migrateFileDir = dirname($pathValue);
                // 查找此目录下的prefix.json
                $prefixJson = $migrateFileDir . '/model.json';
                // 获取描述json信息
                $describeJson = file_get_contents($prefixJson);
                // json解码
                $arrDescription = json_decode($describeJson, 1);
                // 模型名
                $modelName = $arrDescription['model_name'];
                // --profix
                $prefixName     = $arrDescription['prefix_name'];
                $artisanCommand = "php artisan infyom:rollback $modelName scaffold --prefix=$prefixName";
                $commandResult[] = passthru($artisanCommand, $result);
            }
            return $commandResult;
        }
    }
}
