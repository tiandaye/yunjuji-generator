<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:55:36
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 09:56:35
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:publish {sourcePath} {targetPath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'publish';

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
        // 源目录
        $sourcePath = $this->argument('sourcePath');
        // $sourcePath = 'D:\soft\xampp\htdocs\laravel-project\zuixin';
        $sourcePath = str_replace(DIRECTORY_SEPARATOR, '/', $sourcePath);

        // 目标目录
        $targetPath = $this->argument('targetPath');
        $targetPath = str_replace(DIRECTORY_SEPARATOR, '/', $targetPath);

        // 发布 `migration`
        $migrationPath       = '/database/migrations';
        $sourceMigrationPath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $migrationPath);
        $targetMigrationPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $migrationPath);
        $result = $this->copyDir($sourceMigrationPath, $targetMigrationPath);

        // 发布 `Model`
        $modelPath       = '/app/Models';
        $sourceModelPath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $modelPath);
        $targetModelPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $modelPath);
        $result = $this->copyDir($sourceModelPath, $targetModelPath);

        // 发布 `repo`
        $repoPath       = '/app/Repositories';
        $sourceRepoPath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $repoPath);
        $targetRepoPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $repoPath);
        $result = $this->copyDir($sourceRepoPath, $targetRepoPath);

        // 发布 `request`
        $requestPath       = '/app/Http/Requests';
        $sourceRequestPath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $requestPath);
        $targetRequestPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $requestPath);
        $result = $this->copyDir($sourceRequestPath, $targetRequestPath);
        
        // 发布 `Controller`
        $controllerPath       = '/app/Http/Controllers';
        $sourceControllerPath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $controllerPath);
        $targetControllerPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $controllerPath);
        $result = $this->copyDir($sourceControllerPath, $targetControllerPath);

        // 发布 `route`
        $routePath       = '/routes/web';
        $sourceRoutePath = str_replace('/', DIRECTORY_SEPARATOR, $sourcePath . $routePath);
        $targetRoutePath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath . $routePath);
        $result = $this->copyDir($sourceRoutePath, $targetRoutePath);
    }

    /**
    PHP文件目录copy
    @param   string $dirsrc   原目录名称字符串
    @param   string $dirto    目标目录名称字符串
     */
    public function copyDir($dirSrc, $dirTo)
    {
        if (is_file($dirTo)) {
            echo $dirTo . '这不是一个目录';
            return;
        }
        if (!file_exists($dirTo)) {
            $this->mkDirs($dirTo);
        }
        if ($handle = opendir($dirSrc)) {
            while ($filename = readdir($handle)) {
                if ($filename != '.' && $filename != '..') {
                    $subsrcfile = $dirSrc . DIRECTORY_SEPARATOR . $filename;
                    $subtofile  = $dirTo . DIRECTORY_SEPARATOR . $filename;
                    if (is_dir($subsrcfile)) {
                        $this->copyDir($subsrcfile, $subtofile); //再次递归调用copydir
                    }
                    if (is_file($subsrcfile)) {
                        echo '[' . $subsrcfile. '] copy to [' .$subtofile . ']';
                        copy($subsrcfile, $subtofile);
                    }
                }
            }
            closedir($handle);
        }
    }

    /**
     * [mkDirs 循环创建目录]
     * @param  [type]  $dir  [description]
     * @param  integer $mode [description]
     * @return [type]        [description]
     */
    public function mkDirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }

        if (!$this->mkDirs(dirname($dir), $mode)) {
            return false;
        }

        return @mkdir($dir, $mode);
    }

}
