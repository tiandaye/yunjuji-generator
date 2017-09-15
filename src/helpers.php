<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:28:00
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-15 18:09:02
 */

if (!function_exists('yunjuji_tab')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $spaces
     *
     * @return string
     */
    function yunjuji_tab($spaces = 4)
    {
        return str_repeat(' ', $spaces);
    }
}

if (!function_exists('yunjuji_tabs')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $tabs
     * @param int $spaces
     *
     * @return string
     */
    function yunjuji_tabs($tabs, $spaces = 4)
    {
        return str_repeat(infy_tab($spaces), $tabs);
    }
}

if (!function_exists('yunjuji_nl')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     *
     * @return string
     */
    function yunjuji_nl($count = 1)
    {
        return str_repeat(PHP_EOL, $count);
    }
}

if (!function_exists('yunjuji_nls')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     * @param int $nls
     *
     * @return string
     */
    function yunjuji_nls($count, $nls = 1)
    {
        return str_repeat(infy_nl($nls), $count);
    }
}

if (!function_exists('yunjuji_nl_tab')) {
    /**
     * Generates new line char.
     *
     * @param int $lns
     * @param int $tabs
     *
     * @return string
     */
    function yunjuji_nl_tab($lns = 1, $tabs = 1)
    {
        return infy_nls($lns).infy_tabs($tabs);
    }
}

if (!function_exists('yunjuji_get_template_file_path')) {
    /**
     * get path for template file.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function yunjuji_get_template_file_path($templateName, $templateType)
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'yunjuji.generator.path.templates_dir',
            base_path('resources/yunjuji/yunjuji-generator-templates/')
        );

        $path = $templatesPath.$templateName.'.stub';

        if (file_exists($path)) {
            return $path;
        }

        return base_path('vendor/yunjuji/'.$templateType.'/templates/'.$templateName.'.stub');
    }
}

if (!function_exists('yunjuji_get_template')) {
    /**
     * get template contents.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function yunjuji_get_template($templateName, $templateType)
    {
        $path = yunjuji_get_template_file_path($templateName, $templateType);

        return file_get_contents($path);
    }
}

if (!function_exists('yunjuji_fill_template')) {
    /**
     * fill template with variable values.
     *
     * @param array  $variables
     * @param string $template
     *
     * @return string
     */
    function yunjuji_fill_template($variables, $template)
    {
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }

        return $template;
    }
}

if (!function_exists('yunjuji_fill_field_template')) {
    /**
     * fill field template with variable values.
     *
     * @param array                                   $variables
     * @param string                                  $template
     * @param \InfyOm\Generator\Common\GeneratorField $field
     *
     * @return string
     */
    function yunjuji_fill_field_template($variables, $template, $field)
    {
        foreach ($variables as $variable => $key) {
            $template = str_replace($variable, $field->$key, $template);
        }

        return $template;
    }
}

if (!function_exists('yunjuji_fill_template_with_field_data')) {
    /**
     * fill template with field data.
     *
     * @param array                                   $variables
     * @param array                                   $fieldVariables
     * @param string                                  $template
     * @param \InfyOm\Generator\Common\GeneratorField $field
     *
     * @return string
     */
    function yunjuji_fill_template_with_field_data($variables, $fieldVariables, $template, $field)
    {
        $template = yunjuji_fill_template($variables, $template);

        return yunjuji_fill_field_template($fieldVariables, $template, $field);
    }
}

if (!function_exists('yunjuji_model_name_from_table_name')) {
    /**
     * generates model name from table name.
     *
     * @param string $tableName
     *
     * @return string
     */
    function yunjuji_model_name_from_table_name($tableName)
    {
        return ucfirst(camel_case(str_singular($tableName)));
    }
}

if (!function_exists('udate')) {
    /**
     * [udate  年月日时分秒毫秒]
     * @param  string  $format     [格式化]
     * @param  [type]  $utimestamp [description]
     * @param  integer $precision  [精度]
     * @return [type]              [description]
     */
    function udate($format = 'u', $utimestamp = null, $precision = 10000)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp    = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * $precision);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}