<?php

namespace Yunjuji\Generator\Common;

use Illuminate\Support\Str;

class GeneratorFilterField
{
    /** @var string */
    // 字段名
    public $name;
    // 标题
    public $label;
    // 控件类型, [select: 下拉框, multipleSelect: 多选(一般用来配合in和notIn两个需要查询数组的查询类型使用，也可以在where类型的查询中使用), datetime: 日期, date: $filter->equal('column')->datetime(['format' => 'YYYY-MM-DD']), time: $filter->equal('column')->datetime(['format' => 'HH:mm:ss']), day: $filter->equal('column')->datetime(['format' => 'DD']), month: $filter->equal('column')->datetime(['format' => 'MM']), year: $filter->equal('column')->datetime(['format' => 'YYYY']) ]
    public $htmlType;
    // 操作符, [equal: 等于, notEqual: 不等于, like: 模糊匹配, ilike: 不区分大小写, gt: 大于, lt: 小于, between: 范围(可以跟 `time` 或 `datetime` 控件连用), in: 某个范围内(和 `multipleSelect` 控件连用), notIn: 不在某个范围(和 `multipleSelect` 控件连用), date: date, month: month, year: year, where: 就需要自己写了]
    public $operator;
    // 格式
    public $format;
    // 选项
    public $option;

    public static function parseFilterFieldFile($filterFieldInput)
    {
        $field = new self();
        $field->name = $filterFieldInput['name'];
        $field->label = isset($filterFieldInput['label']) ? $filterFieldInput['label'] : $filterFieldInput['name'];
        $field->htmlType = isset($filterFieldInput['htmlType']) ? $filterFieldInput['htmlType'] : '';
        $field->operator = isset($filterFieldInput['operator']) ? $filterFieldInput['operator'] : 'like';
        $field->format = isset($filterFieldInput['format']) ? $filterFieldInput['format'] : '';
        $field->option = isset($filterFieldInput['option']) ? $filterFieldInput['option'] : '';
        return $field;
    }

    public function __get($key)
    {
        // if ($key == 'fieldTitle') {
        //     return Str::title(str_replace('_', ' ', $this->name));
        // }

        return $this->$key;
    }
}
