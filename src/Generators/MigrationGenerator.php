<?php

/**
 * @Author: admin
 * @Date:   2017-09-27 19:19:00
 * @Last Modified by:   admin
 */

namespace Yunjuji\Generator\Generators;

use File;
use Illuminate\Support\Str;
use Yunjuji\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use SplFileInfo;

class MigrationGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /**
     * tian add
     * [$baseTemplateType 基本的模板类型]
     * @var [type]
     */
    private $baseTemplateType;

    /**
     * tian add
     * [$formMode form的模式]
     * @var [type]
     */
    private $formMode = '';

    /**
     * tian add
     * [$formModePrefix description]
     * @var [type]
     */
    private $formModePrefix = '';

    public function __construct($commandData)
    {
        /**
         * tian comment start
         */
        // $this->commandData = $commandData;
        // $this->path = config('infyom.laravel_generator.path.migration', base_path('database/migrations/'));
        // if (!empty($commandData->getOption('generatePath'))) {
        //     $generatePath = $commandData->getOption('generatePath');
        //     $this->path = $generatePath . '/' . 'database/migrations/';
        // }
        /**
         * tian comment end
         */
        
        /**
         * tian add start
         */
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.migration', base_path('database/migrations/'));
        if (!empty($commandData->getOption('generatePath'))) {
            $generatePath = $commandData->getOption('generatePath');
            $this->path = $generatePath . '/' . 'database/migrations/';
        }
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        /**
         * tian add end
         */
    }

    public function generate()
    {
        /**
         * tian comment start
         */
        // $templateData = get_template('migration', 'laravel-generator');

        // $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        // $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

        // $tableName = $this->commandData->dynamicVars['$TABLE_NAME$'];

        // $fileName = date('Y_m_d_His').'_'.'create_'.$tableName.'_table.php';

        // FileUtil::createFile($this->path, $fileName, $templateData);

        // $this->commandData->commandComment("\nMigration created: ");
        // $this->commandData->commandInfo($fileName);
        /**
         * tian comment end
         */
        
        /**
         * tian add start
         */
        // 如果 `migrateBatch` 有值, 说明是修改, 否则是新增
        if ($migrateBatch = $this->commandData->getOption('migrateBatch')) {
            $templateData = yunjuji_get_template($this->formModePrefix . 'update_migration', $this->baseTemplateType);

            $templateData = fill_template($this->commandData->dynamicVars, $templateData);

            $templateData = str_replace('$MIGRATE_BATCH$', $migrateBatch, $templateData);

            $tableName = $this->commandData->dynamicVars['$TABLE_NAME$'];

            $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

            $templateData = str_replace('$UPDATE_FIELDS$', $this->generateUpdateFields($tableName), $templateData);

            $fileName = date('Y_m_d_His').'_'.'update_'.$tableName.$migrateBatch.'_table.php';
        } else {
            $templateData = yunjuji_get_template($this->formModePrefix . 'migration', $this->baseTemplateType);

            $templateData = fill_template($this->commandData->dynamicVars, $templateData);
            
            $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

            $tableName = $this->commandData->dynamicVars['$TABLE_NAME$'];

            $fileName = date('Y_m_d_His').'_'.'create_'.$tableName.'_table.php';
        }

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nMigration created: ");
        $this->commandData->commandInfo($fileName);
        /**
         * tian add end
         */
    }

    /**
     * [generateFields 产生 `migration` 文件中的字段]
     * @return [type] [description]
     */
    private function generateFields()
    {
        $fields = [];
        $foreignKeys = [];
        $createdAtField = null;
        $updatedAtField = null;

        foreach ($this->commandData->fields as $field) {
            if ($field->name == 'created_at') {
                $createdAtField = $field;
                continue;
            } else {
                if ($field->name == 'updated_at') {
                    $updatedAtField = $field;
                    continue;
                }
            }

            $fields[] = $field->migrationText;
            if (!empty($field->foreignKeyText)) {
                $foreignKeys[] = $field->foreignKeyText;
            }
        }

        if ($createdAtField and $updatedAtField) {
            $fields[] = '$table->timestamps();';
        } else {
            if ($createdAtField) {
                $fields[] = $createdAtField->migrationText;
            }
            if ($updatedAtField) {
                $fields[] = $updatedAtField->migrationText;
            }
        }

        if ($this->commandData->getOption('softDelete')) {
            $fields[] = '$table->softDeletes();';
        }

        return implode(infy_nl_tab(1, 3), array_merge($fields, $foreignKeys));
    }

    /**
     * tian add
     * [generateUpdateFields 产生 `migration` 文件中的字段]
     * @return [type] [description]
     */
    private function generateUpdateFields($tableName)
    {
        $fields = [];
        $foreignKeys = [];
        $notChangedTypes = ['increments', 'char', 'double', 'enum', 'mediumInteger', 'timestamp', 'tinyInteger', 'ipAddress', 'json', 'jsonb', 'macAddress', 'mediumIncrements', 'morphs', 'nullableMorphs', 'nullableTimestamps', 'softDeletes', 'timeTz', 'timestampTz', 'timestamps', 'timestampsTz', 'unsignedMediumInteger', 'unsignedTinyInteger', 'uuid'];
        $templateData = yunjuji_get_template($this->formModePrefix . 'migration.has_column', $this->baseTemplateType);

        foreach ($this->commandData->fields as $field) {
            // $dbType = $field->dbInput;
            $fieldType = $field->fieldType;
            if ($field->name == 'created_at') {
                continue;
            } else {
                if ($field->name == 'updated_at') {
                    continue;
                }
            }

            // 保存 `字段存在`
            $fieldExistData = '';
            if (!in_array($fieldType, $notChangedTypes)) {
                $fieldExistData = rtrim($field->migrationText, ';');
                $fieldExistData = str_replace('->index()', '', $fieldExistData);
                $fieldExistData = $fieldExistData . '->change();';
            }

            $fieldTemplateData = $templateData;

            // 替换表名
            $fieldTemplateData = str_replace('$TABLE_NAME$', $tableName, $fieldTemplateData);

            // 替换字段名
            $fieldTemplateData = str_replace('$FIELD_NAME$', $field->name, $fieldTemplateData);

            // 替换 `字段存在`
            $fieldTemplateData = str_replace('$FIELD_EXIST$', $fieldExistData, $fieldTemplateData);
            
            // 替换 `字段不存在`
            $fieldTemplateData = str_replace('$FIELD_NOET_EXIST$', $field->migrationText, $fieldTemplateData);

            $fields[] = $fieldTemplateData;
            // if (!empty($field->foreignKeyText)) {
            //     $foreignKeys[] = $field->foreignKeyText;
            // }
        }

        return implode(infy_nl_tab(1, 3), array_merge($fields, $foreignKeys));
    }

    /**
     * [rollback 回滚]
     * @return [type] [description]
     */
    public function rollback()
    {
        $fileName = 'create_'.$this->commandData->config->tableName.'_table.php';

        /** @var SplFileInfo $allFiles */
        $allFiles = File::allFiles($this->path);

        $files = [];

        foreach ($allFiles as $file) {
            $files[] = $file->getFilename();
        }

        $files = array_reverse($files);

        foreach ($files as $file) {
            if (Str::contains($file, $fileName)) {
                if ($this->rollbackFile($this->path, $file)) {
                    $this->commandData->commandComment('Migration file deleted: '.$file);
                }
                break;
            }
        }
    }
}
