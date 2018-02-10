<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/1/2
 * Time: 12:07
 */

namespace Yunjuji\Generator;


trait Util
{
    /**
     * 如果给定的目录不存在, 创建这个目录
     * @param $dirName string
     */
    private function mkdir($dirName)
    {
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * 如果给定的文件存在, 删除这个文件
     * @param $filePath string 文件存储的路径
     */
    private function deleteFile($filePath)
    {
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 将字符串转换成对应的大驼峰命名
     * @param $var string 变量名字符串
     * @return string 转换之后的大驼峰命名字符串
     */
    private function upperCamelCase($var)
    {
        return ucfirst(camel_case($var));
    }

    /**
     * 将字符串转换成对应的中划线命名
     * @param $var string 变量名字符串
     * @return string 转换之后的中划线命名字符串
     */
    private function middleLineCase($var)
    {
        return str_replace('_', '-', snake_case($var));
    }

    /**
     * @param $str string 需要被替换的模板字符串
     * @param $mapping array 用于替换的的映射数组, 键替换成值$key->$value
     * @return string 返回替换之后的模板字符串
     */
    private function strReplaces($str, $mapping)
    {
        return str_replace(array_keys($mapping), array_values($mapping), $str);
    }

    /**
     * 将model.json文件中解析出来的prefix_name字符串, 返回中间通过点进行拼接, 并且每个部分都是下划线命名的字符串
     * @param $prefixName string model.json文件中解析出来的prefix_name字符串
     * @return string
     */
    private function castPrefixNameToDotRoute($prefixName)
    {
        $arr  = explode('\\', $prefixName);
        $data = [];
        foreach ($arr as $item) {
            $data[] = snake_case($item);
        }
        return implode('.', $data);
    }

    /**
     * 将model.json文件中解析出来的prefix_name字符串, 返回中间通过正斜线进行拼接, 并且每个部分都是下划线命名的字符串
     * @param $prefixName string model.json文件中解析出来的prefix_name字符串
     * @return string
     */
    private function castPrefixNameToForwardSlashRoute($prefixName)
    {
        $arr  = explode('\\', $prefixName);
        $data = [];
        foreach ($arr as $item) {
            $data[] = snake_case($item);
        }
        return implode('/', $data);
    }


}