<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:59:47
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 10:00:24
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DropTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:dropTable {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'drop table';

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
        // 要遍历的文件名
        $fileName = 'data.csv';
        // 获取要遍历的路径
        $path = $this->argument('path');
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        // 获取 `model.xls` 路径【model.xls】
        $paths = $this->getFilePath($path, $fileName);
        foreach ($paths as $path) {
            $this->dropTable($path);
        }
    }

    /**
     * [getFilePath 递归遍历文件]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function getFilePath($path = '', $fileName = "data.csv")
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
     * [dropTable 删表]
     * @return [type] [description]
     */
    public function dropTable($path)
    {
        $path = explode('/', $path);
        // 表名, 如果不是s结尾则加s
        $tableName = $path[count($path) - 2];
        if (substr($tableName, strlen($tableName) - 1, 1) !== 's') {
            $tableName .= 's';
        }
        // 重命名表名
        // Schema::rename($from, $to);
        // 删表
        // Schema::drop('users');
        Schema::dropIfExists($tableName);
    }
}
