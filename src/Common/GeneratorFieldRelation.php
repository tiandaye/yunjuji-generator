<?php

namespace Yunjuji\Generator\Common;

class GeneratorFieldRelation
{
    /** @var string */
    public $type;
    public $inputs;

    public static function parseRelation($relationInput)
    {
        $inputs = explode(',', $relationInput);

        $relation         = new self();
        $relation->type   = array_shift($inputs);
        $relation->inputs = $inputs;

        return $relation;
    }

    public function getRelationFunctionText($commandData)
    {
        $modelName = $this->inputs[0];
        switch ($this->type) {
            case '1t1':
                $functionName  = camel_case($modelName);
                $relation      = 'hasOne';
                $relationClass = 'HasOne';
                break;
            case '1tm':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'hasMany';
                $relationClass = 'HasMany';
                break;
            case 'mt1':
                $functionName  = camel_case($modelName);
                $relation      = 'belongsTo';
                $relationClass = 'BelongsTo';
                break;
            case 'mtm':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'belongsToMany';
                $relationClass = 'BelongsToMany';
                break;
            case 'hmt':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'hasManyThrough';
                $relationClass = 'HasManyThrough';
                break;
            /**
             * tian extends model relations type start
             */
            // 多态morphTo关系
            case 'mht':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'morphTo';
                $relationClass = 'MorphTo';
                break;
            // 多态morphMany关系
            case 'mhm':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'morphMany';
                $relationClass = 'MorphMany';
                break;
            // 多态多对多关联morphToMany关系
            case 'mhtm':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'morphToMany';
                $relationClass = 'MorphToMany';
                break;
            // 多态多对多关联morphedByMany关系
            case 'mhbm':
                $functionName  = camel_case(str_plural($modelName));
                $relation      = 'morphedByMany';
                $relationClass = 'MorphedByMany';
                break;
            /**
             * tian extends model relations type end
             */
            default:
                $functionName  = '';
                $relation      = '';
                $relationClass = '';
                break;
        }

        if (!empty($functionName) and !empty($relation)) {
            return $this->generateRelation($functionName, $relation, $relationClass, $commandData);
        }

        return '';
    }

    private function generateRelation($functionName, $relation, $relationClass, $commandData)
    {
        $inputs    = $this->inputs;
        $modelName = array_shift($inputs);

        // $template = get_template('model.relationship', 'laravel-generator');
        /**
         * 判断不同的关联关系加载不同的 `relationship` 的stub模板 start
         */
        if ($relation == 'morphTo ') {
            $template = get_template('model.relationship_mht', 'laravel-generator');
        } elseif ($relation == 'hasManyThrough') {
            $template = get_template('model.relationship_hmt', 'laravel-generator');
            $template = str_replace('$RELATION_FAR_MODEL_NAME$', array_shift($this->inputs), $template);
        } else {
            $template = get_template('model.relationship', 'laravel-generator');
        }
        /**
         * 判断不同的关联关系加载不同的 `relationship` 的stub模板 end
         */

        $template = str_replace('$RELATIONSHIP_CLASS$', $relationClass, $template);
        $template = str_replace('$FUNCTION_NAME$', $functionName, $template);
        $template = str_replace('$RELATION$', $relation, $template);
        $template = str_replace('$RELATION_MODEL_NAME$', $modelName, $template);

        /**
         * tian add 关联模型的命名空间 start
         */
        if (isset($commandData->namespaceModelMapping[$modelName])) {
            $namespaceModelPos = strrpos($commandData->namespaceModelMapping[$modelName], "\\" . $modelName);
            $namespaceModel = substr($commandData->namespaceModelMapping[$modelName], 0, $namespaceModelPos);
            $template = str_replace('$NAMESPACE_MODEL$', $namespaceModel, $template);
        }
        /**
         * tian add 关联模型的命名空间 end
         */

        if (count($inputs) > 0) {
            $inputFields = implode("', '", $inputs);
            $inputFields = ", '" . $inputFields . "'";
        } else {
            $inputFields = '';
        }

        $template = str_replace('$INPUT_FIELDS$', $inputFields, $template);

        return $template;
    }
}
