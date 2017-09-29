<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:58:06
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 10:01:42
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use DB;
use Excel;
use Illuminate\Console\Command;

class FillDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:fillData {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fill data';

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

        $characterEncoding = 'GBK';//$this->argument('characterEncoding');
        // $characterEncoding = 'UTF-8';
        // 获取全部csv文件的路径
        $filePath = $this->getFilePath($path, 'data.csv');
        // 将csv文件的数据插入数据表
        $result = $this->fillData($filePath, $characterEncoding);
    }

    /**
     * Gets the file path.
     *
     * @param      string  $path      The path
     * @param      string  $fileName  The file name
     *
     * @return     array   The file path.
     */
    public function getFilePath($path = '', $fileName = "data.csv")
    {
        $filePath = [];
        $path     = str_replace(DIRECTORY_SEPARATOR, '/', $path);
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
                $filePath = array_merge($filePath, $this->getFilePath($sub_dir, $fileName));
            } else {
                // 如果文件名等于要找的文件
                if ($file == $fileName) {
                    $sub_dir    = str_replace(DIRECTORY_SEPARATOR, '/', $sub_dir);
                    $filePath[] = $sub_dir;
                }
            }
        }
        // 关闭句柄，释放资源
        closedir($current_dir);
        return $filePath;
    }

    /**
     * 填充数据
     *
     * @param      <type>  $filePath  The file path
     */
    public function fillData($filePath, $characterEncoding = 'UTF-8')
    {

        //for循环, 遍历data.csv路径的数组
        foreach ($filePath as $pathValue) {
            // 选中第一个Sheet, 这样第一行当做属性名
            Excel::selectSheetsByIndex(0)->load($pathValue, function ($reader) use ($pathValue) {
                // 获得所有数据
                $allData = $reader->all()->toArray();

                $num = count($allData);
                // 保存要插入的数据
                $data = [];
                $dir  = explode('/', $pathValue);
                // 表名, 如果不是s结尾则加s
                $tableName = $dir[count($dir) - 2];
                if (substr($tableName, strlen($tableName) - 1, 1) !== 's') {
                    $tableName .= 's';
                }
                // 每次插入多少条
                $batchCount = 500;
                $k          = 0;
                // 循环插入
                for ($i = 0; $i < $num; $i++) {
                    $k++;
                    // array_map, trim
                    $allData[$i] = array_map("trim", $allData[$i]);
                    $tempData    = $allData[$i];
                    // created_at, updated_at
                    $tempData['created_at'] = date('Y-m-d H:i:s');
                    $tempData['updated_at'] = date('Y-m-d H:i:s');
                    $data[]                 = $tempData;
                    //判断是否要插入一次数据库
                    if ($k % $batchCount == 0) {
                        DB::beginTransaction();
                        try {
                            //插入到表中
                            if (!empty($data)) {
                                DB::table($tableName)->insert($data);
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback(); //事务回滚
                            echo $e->getMessage();
                            dd($e->getCode());
                        }
                        // 重置数组
                        $data = [];
                    }
                }

                /**
                 * 判断是否还没有插的数据
                 */
                if (count($data) > 0) {
                    DB::beginTransaction();
                    try {
                        //插入到串码库中
                        if (!empty($data)) {
                            DB::table($tableName)->insert($data);
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback(); //事务回滚
                        echo $e->getMessage();
                        dd($e->getCode());
                    }
                }
            }, $characterEncoding);
        }
    }
}
