<?php

/**
 * @Author: admin
 * @Date:   2017-09-29 09:41:05
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 10:01:09
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Excel;
use Illuminate\Console\Command;

class GenerateFieldJsonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yunjuji:generateFieldJson {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate field json';

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
        $fileName = 'model.csv';
        // csv编码
        $characterEncoding = 'GBK'; //gb2312|GBK//ignore|GBK//IGNORE
        // 因为GB2312表示的是简体中文，不支持像"囧"之类的更为复杂的汉字以及一些特殊字符，这当然会报错了，解决办法有两种：
        // 1. 扩大输出字符编码的范围，如iconv('UTF-8', 'GBK', '囧')，则可以正确地输出，因为GBK支持的字符范围更广；
        // 2. 在输出的字符编码字符串后面加上"//IGNORE"，如iconv('UTF-8', 'GB2312//IGNORE', '囧')，这样做其实是忽略了不能转换的字符，避免了出错但却不能够正确地输出(即空白不、输出)。

        // 转换字符串编码iconv与mb_convert_encoding的区别
        // iconv — Convert string to requested character encoding(PHP 4 >= 4.0.5, PHP 5)
        // mb_convert_encoding — Convert character encoding(PHP 4 >= 4.0.6, PHP 5)
        // 用法：
        // string mb_convert_encoding ( string str, string to_encoding [, mixed from_encoding] )
        // 需要先启用 mbstring 扩展库，在 php.ini里将; extension=php_mbstring.dll 前面的 ; 去掉

        // string iconv ( string in_charset, string out_charset, string str )
        // 注意：
        // 第二个参数，除了可以指定要转化到的编码以外，还可以增加两个后缀：//TRANSLIT 和 //IGNORE，
        // 其中：
        // //TRANSLIT 会自动将不能直接转化的字符变成一个或多个近似的字符，
        // //IGNORE 会忽略掉不能转化的字符，而默认效果是从第一个非法字符截断。
        // Returns the converted string or FALSE on failure.
        // 例子：
        // $content = iconv("GBK", "UTF-8", $content);
        // $content = mb_convert_encoding($content, "UTF-8", "GBK");

        // 获取要遍历的路径
        $path = $this->argument('path');
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        // 获取model.xls路径【model.xls】
        $paths = $this->getFilePath($path, $fileName);

        $total        = 0;
        $successCount = 0;
        $errorCount   = 0;
        // 遍历
        foreach ($paths as $path_key => $path_value) {
            $total++;
            if ($this->generateJson($path_value, $characterEncoding)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        echo "total:" . $total . ", error:" . $errorCount . ", success:" . $successCount . PHP_EOL;
    }

    /**
     * [getFilePath 递归遍历文件]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function getFilePath($path = '', $fileName = "model.csv")
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
     * [generateJson 产生field json文件]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function generateJson($path, $characterEncoding = 'UTF-8')
    {
        $baseDir  = dirname($path);
        $fileName = $baseDir . '/' . 'fields.json';
        echo 'file:' . $fileName . ' start generate!' . PHP_EOL;
        $gFlag = true;

        try {
            Excel::selectSheetsByIndex(0)->load($path, function ($reader) use ($path, &$gFlag) {
                // 获得所有数据
                $data = $reader->all()->toArray();
                // dd($data);
                // 总记录数
                // $num = count($data);
                $num = 1;
                // $nowTime = date('Y-m-d H:i:s');

                // fields用
                $fieldSymbol1 = ';';
                $fieldSymbol2 = ':';
                // foriegns用
                $foriegnSymbol1 = ';';
                $foriegnSymbol2 = ':';
                $foriegnSymbol3 = ',';
                // 是否需要编辑
                $inFormSymbol1 = ';';
                // 是否显示在table
                $inIndexSymbol1 = ';';
                // 校验
                $validationSymbol1 = ';';
                $validationSymbol2 = ':';
                // htmlType
                $htmlTypeSymbol1 = ';';
                $htmlTypeSymbol2 = ':';
                // nullable
                $nullableSymbol1 = ';';
                // index
                $indexSymbol1 = ';';
                // dbTypes
                $dbTypeSymbol1 = ';';
                $dbTypeSymbol2 = ':';
                // tags
                $tagSymbol1 = ';';
                // relations
                $relationSymbol1 = ';';
                // 无符号【unsigned】
                $unsignedSymbol1 = ';';
                // 显示字段
                $displayFieldSymbol1 = ';';
                $displayFieldSymbol2 = ':';
                // 选项
                $optionSymbol1 = ';';
                $optionSymbol2 = '=';

                // 保存字段[现在label和title默认相同][例子: vasset_no: 视频编号, name: 名称]
                $fields = [];
                // 外键[例子: vcat_no: vcats; vseason_no: vseasons, vseason_no]
                $foriegns = [];
                // 是否需要编辑[例子: f1;f2]
                $inForms = [];
                // 是否需要显示[table]
                $inIndexs = [];
                // 校验
                $validations = [];
                // htmlType
                $htmlTypes = [];
                // nullable
                $nullables = [];
                // index[索引]
                $indexs = [];
                // 数据库字段类型
                $dbTypes = [];
                // tag关系
                $tags = [];
                // 保存关联关系
                $relations = [];
                // 无符号
                $unsigneds = [];
                // 显示字段
                $displayFields = [];
                // 控件选项
                $options = [];

                // 循环excel文件中的数据
                for ($i = 0; $i < $num; $i++) {
                    // $data[$i] = iconv("GBK", "UTF-8", $data[$i]['fields']);
                    // dd($data);
                    // $content = mb_convert_encoding($content, "UTF-8", "GBK");

                    // 保存字段信息
                    $tempFields = [];
                    // 保存外键信息
                    $tempForiegns = [];
                    // 是否可编辑
                    $tempInForms = [];
                    // 是否显示在表格
                    $tempInIndexs = [];
                    // 校验
                    $tempValidations = [];
                    // htmlType
                    $tempHtmlTypes = [];
                    // nullable
                    $tempNullables = [];
                    // index
                    $tempIndexs = [];
                    // 数据库字段类型
                    $tempDbTypes = [];
                    // tag关系
                    $tempTags = [];
                    // 保存关联关系
                    $tempRelations = [];
                    // 无符号
                    $tempUnsigneds = [];
                    // 显示字段
                    $tempDisplayFields = [];
                    // 控件选项
                    $tempOptions = [];

                    /**
                     * fields[字段处理]
                     */
                    if (isset($data[$i]['fields'])) {
                        $tempFields = trim($data[$i]['fields']);
                    }
                    if (empty($tempFields)) {
                        echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[fields-error1]:" . "fields field required!" . PHP_EOL;
                        $gFlag = false;
                        return false;
                    }
                    // 先以 `;` 分割
                    $tempFields = explode($fieldSymbol1, $data[$i]['fields']);
                    foreach ($tempFields as $key => $value) {
                        // $arr = array(',', '，', '.', '。', ';');
                        // $data = array();
                        // $b = preg_match('/,|@|!|.|。/i', $s, $data);

                        $tempField = explode($fieldSymbol2, $value);
                        if (count($tempField) != 2 && count($tempField) != 3) {
                            echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[fields-error2]:" . "出现非法标点符号!" . PHP_EOL;
                            $gFlag = false;
                            return false;
                        }
                        if (count($tempField) == 3) {
                            $fields[trim($tempField[0])] = ['label' => trim($tempField[1]), 'title' => trim($tempField[2])];
                        } else {
                            $fields[trim($tempField[0])] = ['label' => trim($tempField[1]), 'title' => trim($tempField[1])];
                        }

                    }
                    // var_dump($fields);

                    /**
                     * foriegns[外键处理]
                     */
                    if (isset($data[$i]['foriegns'])) {
                        $tempForiegns = trim($data[$i]['foriegns']);
                    }
                    if (!empty($tempForiegns)) {
                        // 先以 `;` 分割
                        $tempForiegns = explode($foriegnSymbol1, $data[$i]['foriegns']);
                        foreach ($tempForiegns as $key => $value) {
                            $tempForiegn = explode($foriegnSymbol2, $value);
                            // 这个数组用来保存json中可以用的值, 最后用`,`连接
                            $arrForiegn     = [];
                            $tempArrForiegn = [];
                            if (count($tempForiegn) != 2) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[foriegns-error1]:" . "参数有误, 请确认是否用 `:` 分割!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $tempArrForiegn = explode($foriegnSymbol3, trim($tempForiegn[1]));
                            $arrForiegn[]   = 'foreign';
                            if (count($tempArrForiegn) == 2) {
                                $arrForiegn[] = trim($tempArrForiegn[0]);
                                $arrForiegn[] = trim($tempArrForiegn[1]);
                            } else {
                                $arrForiegn[] = trim($tempArrForiegn[0]);
                                $arrForiegn[] = trim($tempForiegn[0]);
                            }

                            $foriegns[trim($tempForiegn[0])] = implode(',', $arrForiegn);
                        }
                    }
                    // var_dump($foriegns);

                    /**
                     * 是否需要编辑[form]
                     */
                    if (isset($data[$i]['informs'])) {
                        $tempInForms = trim($data[$i]['informs']);
                    }
                    if (!empty($tempInForms)) {
                        // 先以 `;` 分割
                        $tempInForms = explode($inFormSymbol1, $data[$i]['informs']);
                        foreach ($tempInForms as $key => $value) {
                            $inForms[trim($value)] = true;
                        }
                    }
                    // var_dump($inForms);

                    /**
                     * 是否需要显示[table]
                     */
                    if (isset($data[$i]['inindexs'])) {
                        $tempInIndexs = trim($data[$i]['inindexs']);
                    }
                    if (!empty($tempInIndexs)) {
                        // 先以 `;` 分割
                        $tempInIndexs = explode($inIndexSymbol1, $data[$i]['inindexs']);
                        foreach ($tempInIndexs as $key => $value) {
                            $inIndexs[trim($value)] = true;
                        }
                    }
                    // var_dump($inIndexs);

                    /**
                     * 是否无符号【unsigned】
                     */
                    if (isset($data[$i]['unsigneds'])) {
                        $tempUnsigneds = trim($data[$i]['unsigneds']);
                    }
                    if (!empty($tempUnsigneds)) {
                        // 先以 `;` 分割
                        $tempUnsigneds = explode($unsignedSymbol1, $data[$i]['unsigneds']);
                        foreach ($tempUnsigneds as $key => $value) {
                            $unsigneds[trim($value)] = true;
                        }
                    }
                    // var_dump($unsigneds);

                    /**
                     * 校验
                     */
                    if (isset($data[$i]['validations'])) {
                        $tempValidations = trim($data[$i]['validations']);
                    }
                    if (!empty($tempValidations)) {
                        // 先以 `;` 分割
                        $tempValidations = explode($validationSymbol1, $data[$i]['validations']);
                        foreach ($tempValidations as $key => $value) {
                            // 再用 `:` 分割
                            $flag = strpos($value, $validationSymbol2);
                            if (!$flag) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[validations-error1]:" . "参数有误!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $validations[trim(substr($value, 0, $flag))] = trim(substr($value, $flag + 1));
                        }
                    }
                    // var_dump($validations);

                    /**
                     * htmlType【控件类型】
                     */
                    if (isset($data[$i]['htmltypes'])) {
                        $tempHtmlTypes = trim($data[$i]['htmltypes']);
                    }
                    if (!empty($tempHtmlTypes)) {
                        // 先以 `;` 分割
                        $tempHtmlTypes = explode($htmlTypeSymbol1, $data[$i]['htmltypes']);
                        foreach ($tempHtmlTypes as $key => $value) {
                            // 以 `:` 分割
                            $tempHtmlType = explode($htmlTypeSymbol2, $value);
                            if (count($tempHtmlType) != 2) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[htmltypes-error1]:" . "参数有误, 请确认是否用 `:` 分割!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $htmlTypes[trim($tempHtmlType[0])] = trim($tempHtmlType[1]);
                        }
                    }
                    // var_dump($htmlTypes);

                    /**
                     * nullable【可以为null的字段】
                     */
                    if (isset($data[$i]['nullables'])) {
                        $tempNullables = trim($data[$i]['nullables']);
                    }
                    if (!empty($tempNullables)) {
                        // 先以 `;` 分割
                        $tempNullables = explode($nullableSymbol1, $data[$i]['nullables']);
                        foreach ($tempNullables as $key => $value) {
                            $nullables[trim($value)] = true;
                        }
                    }
                    // var_dump($nullables);

                    /**
                     * index[索引]
                     */
                    if (isset($data[$i]['indexs'])) {
                        $tempIndexs = trim($data[$i]['indexs']);
                    }
                    if (!empty($tempIndexs)) {
                        // 先以 `;` 分割
                        $tempIndexs = explode($indexSymbol1, $data[$i]['indexs']);
                        foreach ($tempIndexs as $key => $value) {
                            $indexs[trim($value)] = true;
                        }
                    }
                    // var_dump($indexs);

                    /**
                     * 数据库字段类型
                     */
                    if (isset($data[$i]['dbtypes'])) {
                        $tempDbTypes = trim($data[$i]['dbtypes']);
                    }
                    if (!empty($tempDbTypes)) {
                        // 先以 `;` 分割
                        $tempDbTypes = explode($dbTypeSymbol1, $data[$i]['dbtypes']);
                        foreach ($tempDbTypes as $key => $value) {
                            // 以 `:` 分割
                            $tempDbType = explode($dbTypeSymbol2, $value);
                            if (count($tempDbType) != 2) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[dbtypes-error1]:" . "参数有误, 请确认是否用 `:` 分割!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $dbTypes[trim($tempDbType[0])] = trim($tempDbType[1]);
                        }
                    }
                    // var_dump($dbTypes);

                    /**
                     * displayFields【显示字段】
                     */
                    if (isset($data[$i]['displayfields'])) {
                        $tempDisplayFields = trim($data[$i]['displayfields']);
                    }
                    if (!empty($tempDisplayFields)) {
                        // 先以 `;` 分割
                        $tempDisplayFields = explode($displayFieldSymbol1, $data[$i]['displayfields']);
                        foreach ($tempDisplayFields as $key => $value) {
                            // 以 `:` 分割
                            $tempDisplayField = explode($displayFieldSymbol2, $value);
                            if (count($tempDisplayField) != 2) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[displayfields-error1]:" . "参数有误, 请确认是否用 `:` 分割!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $displayFields[trim($tempDisplayField[0])] = trim($tempDisplayField[1]);
                        }
                    }
                    // var_dump($displayFields);

                    /**
                     * options【选项】
                     */
                    if (isset($data[$i]['options'])) {
                        $tempOptions = trim($data[$i]['options']);
                    }
                    if (!empty($tempOptions)) {
                        // 先以 `;` 分割
                        $tempOptions = explode($optionSymbol1, $data[$i]['options']);
                        foreach ($tempOptions as $key => $value) {
                            // 以 `:` 分割
                            $tempOption = explode($optionSymbol2, $value);
                            if (count($tempOption) != 2) {
                                echo "[path:" . $path . "]-[line:" . __LINE__ . "]-[options-error1]:" . "参数有误, 请确认是否用 `:` 分割!" . PHP_EOL;
                                $gFlag = false;
                                return false;
                            }
                            $options[trim($tempOption[0])] = json_decode(trim($tempOption[1]), true);
                        }
                    }
                    // var_dump($options);

                    /**
                     * tags[多对多]
                     */
                    if (isset($data[$i]['tags'])) {
                        $tempTags = trim($data[$i]['tags']);
                    }
                    if (!empty($tempTags)) {
                        // 先以 `;` 分割
                        $tempTags = explode($tagSymbol1, $data[$i]['tags']);
                        foreach ($tempTags as $key => $value) {
                            $tags[] = trim($value);
                        }
                    }
                    // var_dump($tags);

                    /**
                     * relations【当1对1】
                     */
                    if (isset($data[$i]['relations'])) {
                        $tempRelations = trim($data[$i]['relations']);
                    }
                    if (!empty($tempRelations)) {
                        // 先以 `;` 分割
                        $tempRelations = explode($relationSymbol1, $data[$i]['relations']);
                        foreach ($tempRelations as $key => $value) {
                            $relations[] = trim($value);
                        }
                    }
                    // var_dump($relations);

                    $fieldJson = [];
                    // id字段
                    $fieldJson[] = ["name" => "id", "dbType" => "increments", "htmlType" => "", "validations" => "", "searchable" => false, "fillable" => false, "primary" => true, "inForm" => false, "inIndex" => false];
                    // 吐出json文件, 字段和关联关系分开
                    foreach ($fields as $field_key => $field_value) {
                        $field = [];
                        // name, label, title属性
                        $field["name"]  = $field_key;
                        $field["label"] = $field_value['label'];
                        $field["title"] = $field_value['title'];
                        // 是否需要编辑[form中]
                        if (array_key_exists($field_key, $inForms)) {
                            $field['inForm'] = true;
                        } else {
                            $field['inForm'] = false;
                        }
                        // 是否需要显示[table中]
                        if (array_key_exists($field_key, $inIndexs)) {
                            $field['inIndex'] = true;
                        } else {
                            $field['inIndex'] = false;
                        }
                        // 校验
                        if (array_key_exists($field_key, $validations)) {
                            $field['validations'] = $validations[$field_key];
                        } else {
                            $field['validations'] = "";
                        }
                        // htmlType-默认给text
                        if (array_key_exists($field_key, $htmlTypes)) {
                            $field['htmlType'] = $htmlTypes[$field_key];
                        } else {
                            $field['htmlType'] = "text";
                        }
                        // dbType
                        if (array_key_exists($field_key, $dbTypes)) {
                            $strDbType = $dbTypes[$field_key];
                            // 是否无符号
                            if (array_key_exists($field_key, $unsigneds)) {
                                $strDbType .= ':' . 'unsigned';
                            }
                            // 是否有外键
                            if (array_key_exists($field_key, $foriegns)) {
                                $strDbType .= ':' . $foriegns[$field_key];
                            }
                            // 是否nullable
                            if (array_key_exists($field_key, $nullables)) {
                                $strDbType .= ':' . 'nullable';
                            }
                            // 是否索引
                            if (array_key_exists($field_key, $indexs)) {
                                $strDbType .= ':' . 'index';
                            }
                            $field['dbType'] = $strDbType;
                        } else {
                            // 数据库要是字段类型没给, 默认给他string, 200
                            $field['dbType'] = "string, 200";
                        }
                        // displayField【显示字段】
                        if (array_key_exists($field_key, $displayFields)) {
                            $field['displayField'] = $displayFields[$field_key];
                        }
                        // option【选项】
                        if (array_key_exists($field_key, $options)) {
                            $field['options'] = $options[$field_key];
                        }
                        $fieldJson[] = $field;
                    }

                    // 遍历tags关系
                    foreach ($tags as $tag_key => $tag_value) {
                        $relation             = [];
                        $relation['type']     = 'relation';
                        $relation['relation'] = $tag_value;
                        $fieldJson[]          = $relation;
                    }
                    // 遍历关联关系
                    foreach ($relations as $relation_key => $relation_value) {
                        $relation             = [];
                        $relation['type']     = 'relation';
                        $relation['relation'] = $relation_value;
                        $fieldJson[]          = $relation;
                    }

                    // 新建, 修改字段
                    // $fieldJson[] = ["name" => "created_at", "dbType" => "timestamp", "htmlType" => "", "validations" => "", "searchable" => false, "fillable" => false, "primary" => false, "inForm" => false, "inIndex" => false];
                    // $fieldJson[] = ["name" => "updated_at", "dbType" => "timestamp", "htmlType" => "", "validations" => "", "searchable" => false, "fillable" => false, "primary" => false, "inForm" => false, "inIndex" => false];
                    // 数组转josn
                    $strFieldJson = json_encode($fieldJson);
                    // 写文件
                    $baseDir  = dirname($path);
                    $fileName = $baseDir . '/' . 'fields.json';
                    // echo $strFieldJson;
                    // echo $fileName;
                    $this->writeFile($fileName, $strFieldJson);
                    //给权限
                    chmod($fileName, 0777);
                    echo 'file:' . $fileName . ' generate success!' . PHP_EOL . PHP_EOL;
                }
            }, $characterEncoding); //->convert('xls');// , $characterEncoding
        } catch (Exception $e) {
            dd(123456);
            print $e->getMessage();
            exit();
        }

        return $gFlag;
    }

    /**
     * [writeFile 写文件]
     * @param  [type] $fname [文件名]
     * @param  [type] $str   [内容]
     * @return [type]        [description]
     */
    public function writeFile($fname, $str)
    {
        $fp = fopen($fname, "w") or die($fname . "打开文件错误");
        fputs($fp, $str) or die($fname . "写入文件错误");
        fclose($fp) or die($fname . "关闭文件错误");
    }
}
