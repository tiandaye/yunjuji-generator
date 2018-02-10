<?php

namespace Yunjuji\Generator\Generators\Scaffold;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use Yunjuji\Generator\Common\CommandData;
use Yunjuji\Generator\Generators\Scaffold\RequestGenerator;

class ControllerGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var string */
    private $fileName;

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

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path        = $commandData->config->pathController;

        /**
         * tian comment start
         */
        // $this->templateType = config('infyom.laravel_generator.templates', 'core-templates');
        /**
         * tian comment end
         */

        /**
         * tian add start
         */
        $this->templateType     = config('yunjuji.generator.templates.extend', 'core-templates');
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        /**
         * tian add end
         */

        $this->fileName = $this->commandData->modelName . 'Controller.php';
    }

    /**
     * [generate 【产生】]
     * @return [type] [description]
     */
    public function generate()
    {
        /**
         * tian comment start
         */
        // if ($this->commandData->getAddOn('datatables')) {
        //     $templateData = get_template('scaffold.controller.datatable_controller', 'laravel-generator');

        //     $this->generateDataTable();
        // } else {
        //     $templateData = get_template('scaffold.controller.controller', 'laravel-generator');

        //     $paginate = $this->commandData->getOption('paginate');

        //     if ($paginate) {
        //         $templateData = str_replace('$RENDER_TYPE$', 'paginate('.$paginate.')', $templateData);
        //     } else {
        //         $templateData = str_replace('$RENDER_TYPE$', 'all()', $templateData);
        //     }
        // }
        /**
         * tian comment end
         */

        /**
         * tian add start
         */
        // 不同的模式选择不用的模板
        if ($this->formMode) {
            $templateData = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.controller', $this->baseTemplateType);
        } else {
            $templateData = yunjuji_get_template('scaffold.controller.controller', $this->baseTemplateType);
        }

        // 【form表单模式为laravel-admin时使用，添加options字段】
        if ($this->formMode) {
            if ($this->formMode == 'laravel-backpack') {
                // 字段列表
                $strBackpackFieldList = '';
                // 如果需要则使用rbac的模板
                if ($this->commandData->getOption('rbac')) {
                    $strFieldTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.rbac_field', $this->baseTemplateType);
                } else {
                    // 单个字段的模板
                    $strFieldTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.field', $this->baseTemplateType);
                }

                // 遍历普通字段和m2m关联字段
                foreach (array_merge($this->commandData->fields, $this->commandData->hasM2mRelationFields) as $key => $field) {
                    if (!$field->inForm) {
                        continue;
                    }

                    // 判断是否有name字段
                    $flag = false;
                    // 字段选项
                    $arrOptions = [];
                    // 控件类型
                    if (!empty($field->htmlType)) {
                        $arrOptions['type'] = $field->htmlType;
                    }
                    // name
                    if (!empty($field->name)) {
                        $arrOptions['name']  = $field->name;
                        $arrOptions['label'] = $field->name;
                        $flag                = true;
                    }
                    // label
                    if (!empty($field->label)) {
                        $arrOptions['label'] = $field->label;
                    }
                    // `displayField` 通过它去判断需要那个字段当可以 `key`, 那个字段当 `value`
                    if (count($field->displayField) > 0) {
                        $arrOptions['attribute'] = $field->displayField[2];
                        // 临时变量模型名【因为现在提供的是表名, 默认认为模型名有s】
                        $tempModelName = $relationField['displayField'][0];
                        if (substr($tempModelName, -1) != 's') {
                            $tempModelName .= 's';
                        }
                        $arrOptions['model'] = $this->commandData->config->nsModel . '\\' . $tempModelName;
                        $arrOptions['model'] = str_replace("\\\\", "\\", $arrOptions['model']);
                    }
                    if ($flag) {
                        if (!empty($field->options)) {
                            $arrOptions = array_merge($arrOptions, json_decode(json_encode($field->options), true));
                        }
                        // 针对rbac时每个字段给它加上rbac控制,所以要进行变量替换
                        $strTempFieldTemplate = $strFieldTemplate;
                        $strTempFieldTemplate = fill_template_with_field_data(
                            $this->commandData->dynamicVars,
                            $this->commandData->fieldNamesMapping,
                            $strTempFieldTemplate,
                            $field
                        );
                        $strBackpackFieldList .= str_replace('$OPTIONS$', var_export($arrOptions, true), $strTempFieldTemplate . "\n");
                    }
                }
                // 替换模板
                $templateData = str_replace('$BACKPACK_FIELD_LIST$', $strBackpackFieldList, $templateData);
            } else if ($this->formMode == 'laravel-admin') {
                /**
                 * 获取标签相关模板 start
                 */
                $strTagBatchDestoryTempalte = '';
                $strTagDestoryTempalte      = '';
                $strTagEditTempalte         = '';
                $strTagFormTempalte         = '';
                $strTagGridTempalte         = '';
                $strTagStoreTempalte        = '';
                $strTagUpdateTempalte       = '';

                if ($this->commandData->getOption('isTagging')) {
                    // 获取标签批量删除模板
                    $strTagBatchDestoryTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.batch_destroy', $this->baseTemplateType);
                    // 获取标签删除模板
                    $strTagDestoryTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.destroy', $this->baseTemplateType);
                    // 获取标签编辑模板
                    $strTagEditTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.edit', $this->baseTemplateType);
                    // 获取标签Form模板
                    $strTagFormTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.form', $this->baseTemplateType);
                    // 获取标签Grid模板
                    $strTagGridTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.grid', $this->baseTemplateType);
                    // 获取标签保存模板
                    $strTagStoreTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.store', $this->baseTemplateType);
                    // 获取标签更新文档
                    $strTagUpdateTempalte = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.tag.update', $this->baseTemplateType);
                }
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_STORE$', $strTagStoreTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_EDIT$', $strTagEditTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_UPDATE$', $strTagUpdateTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_BATCH_DESTROY$', $strTagBatchDestoryTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_DESTROY$', $strTagDestoryTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_FORM$', $strTagFormTempalte, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_TAGGING_GRID$', $strTagGridTempalte, $templateData);


                /**
                 * 获取标签相关模板 end
                 */
                // 获取 `grid` 的 `column` 的普通模板
                $strGridColumnTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.column', $this->baseTemplateType);
                // 获取 `grid` 的 `mtm column` 的模板
                $strGridMtmColumnTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.m2m_column', $this->baseTemplateType);
                // 获取 `grid` 的 `image column` 的模板
                $strGridImgColumnTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.image_column', $this->baseTemplateType);
                // `form` 的 `get options`. 获取遍历数据的模板
                $strFormGetOptionsTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.form-fields.display.get-options', $this->baseTemplateType);
                // `form` 的 `option`. 选项的模板[->option($OPTIONS$);]
                $strFormOptionTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.form-fields.options', $this->baseTemplateType);
                // `form` 的 `field`. [$form->$FORMOPTIONS$;]
                $strFormFieldTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.form-fields.form', $this->baseTemplateType);
                // `filter` 的 `options`. 过滤区域的选项值
                $strFilterGetOptionsTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.filter.get-options', $this->baseTemplateType);
                // 用来拼接 `laravel-admin` `form` 中的字段
                $strLaravelAdminFieldList = '';
                // 用来拼接 `laravel-admin` 的 `grid` 中的 `column` 字段
                $strLaravelAdminColumnList = '';
                // 用来凭借 `laravel-admin` 的 `filter` 中的字段
                $strLaravelAdminFilterFieldList = '';
                // 模型的命名空间
                $namespaceModelList = [];
                // 增加多对多关联的store, update, destroy方法
                $strModelRelationsStore   = '';
                $strModelRelationsUpdate  = '';
                $strModelRelationsDestroy = '';
                $m2mFlag                  = false;
                // 增加图片上传的store, update方法
                $strUploadImageStore  = '';
                $strUploadImageUpdate = '';
                /**
                 * relations crud start
                 */
                // 多对多关联的 `store`, `update`, `destroy`方法, 遍历有关联的字段, 现在只处理对多对关系
                foreach ($this->commandData->hasRelationFields as $key => $relationField) {
                    // 判断关联关系
                    if ($relationField['type'] == 'mtm') {
                        $m2mFlag = true;
                        // `mtm` 有五个参数, `1t1` 有四个参数
                        $tempRelationName = camel_case($relationField['inputs'][0]);
                        if (substr($tempRelationName, -1) != 's') {
                            $tempRelationName .= 's';
                        }
                        // 增加多对多关联的 `store`, `update`, `destroy`方法
                        // 新增
                        $mtmStoreTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relations.mtm.store', $this->baseTemplateType);
                        $mtmStoreTemplateData = fill_template($this->commandData->dynamicVars, $mtmStoreTemplateData);
                        // 替换字段【关联名和对应的字段表】
                        $mtmStoreTemplateData = str_replace('$RELATION_NAME$', $tempRelationName, $mtmStoreTemplateData);
                        $mtmStoreTemplateData = str_replace('$FIELD_NAME$', $tempRelationName, $mtmStoreTemplateData);
                        // $mtmStoreTemplateData = yunjuji_tab(4*3) . $mtmStoreTemplateData;
                        $strModelRelationsStore .= $mtmStoreTemplateData;

                        // 编辑
                        $mtmUpdateTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relations.mtm.update', $this->baseTemplateType);
                        $mtmUpdateTemplateData = fill_template($this->commandData->dynamicVars, $mtmUpdateTemplateData);
                        // 替换字段【关联名和对应的字段表】
                        $mtmUpdateTemplateData = str_replace('$RELATION_NAME$', $tempRelationName, $mtmUpdateTemplateData);
                        $mtmUpdateTemplateData = str_replace('$FIELD_NAME$', $tempRelationName, $mtmUpdateTemplateData);
                        // $mtmUpdateTemplateData = yunjuji_tab(4*3) . $mtmUpdateTemplateData;
                        $strModelRelationsUpdate .= $mtmUpdateTemplateData;

                        // 删除
                        $mtmDestoryTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relations.mtm.destroy', $this->baseTemplateType);
                        $mtmDestoryTemplateData = fill_template($this->commandData->dynamicVars, $mtmDestoryTemplateData);
                        // 替换字段【关联名和对应的字段表】
                        $mtmDestoryTemplateData = str_replace('$RELATION_NAME$', $tempRelationName, $mtmDestoryTemplateData);
                        $mtmDestoryTemplateData = str_replace('$RELATION_FIELD$', $relationField['inputs'][2], $mtmDestoryTemplateData);
                        // $mtmDestoryTemplateData = yunjuji_tab(4*3) . $mtmDestoryTemplateData;
                        $strModelRelationsDestroy .= $mtmDestoryTemplateData;
                    } else if ($relationField['type'] == '1t1') {
                    } else if (in_array($relationField['type'], array('hmt', 'mhm', 'mhtm'))) {
                    }
                }
                if ($m2mFlag) {
                    $templateM2mStore = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.relations.mtm.store', $this->baseTemplateType);
                    $templateM2mUpdate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.relations.mtm.update', $this->baseTemplateType);
                    // 多对多关联的 `store`, `update`, `destroy` 方法
                    // 新增
                    $strModelRelationsStore = str_replace('$MODEL_M2M_RELATIONS_STORE$', $strModelRelationsStore, $templateM2mStore);
                    // 编辑
                    $strModelRelationsUpdate = str_replace('$MODEL_M2M_RELATIONS_UPDATE$', $strModelRelationsUpdate, $templateM2mUpdate);
                }
                $templateData = str_replace('$LARAVEL_ADMIN_MODEL_M2M_RELATIONS_STORE$', $strModelRelationsStore, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_MODEL_M2M_RELATIONS_UPDATE$', $strModelRelationsUpdate, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_MODEL_M2M_RELATIONS_DESTORY$', $strModelRelationsDestroy, $templateData);
                // 遍历关联关系字段
                $detailButtonTemplate = '';
                $batchBelongTemplate  = '';
                $batchEditTemplate    = '';
                $detailTab            = '';
                $detailTabItem        = '';
                $detailTabGrid        = '';
                $flag                 = true;
                foreach ($this->commandData->relations as $key => $relation) {
                    // 如果是hasMany, 在grid中添加详情按钮
                    if ($relation->type == '1tm') {
                        // 获取详情按钮的模板数据
                        $detailButtonTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.detail_button', $this->baseTemplateType);

                        if ($flag) {
                            // 如果有一对多, 或者多对多, 获取详情按钮点进去的视图模板
                            $detailTab = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab', $this->baseTemplateType);
                            $flag      = false;
                        }
                        $relationName = $relation->inputs[0];
                        // 获取里面tab项的内容
                        $detailTabItemTemp = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab_item', $this->baseTemplateType);
                        $detailTabItemTemp = str_replace('$RELATION_NAME$', $relationName, $detailTabItemTemp);
                        $detailTabItem     .= str_replace('$RELATION_NAME_CAMEL$', camel_case($relationName), $detailTabItemTemp) . PHP_EOL;
                        // 获取tab_content里的内容
                        $detailTabGridTemp = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab_item_content', $this->baseTemplateType);
                        $detailTabGridTemp = str_replace('$RELATION_NAME$', $relationName, $detailTabGridTemp);
                        $detailTabGridTemp = str_replace('$RELATION_NAME_CAMEL$', camel_case($relationName), $detailTabGridTemp);
                        $detailTabGrid     .= str_replace('$RELATION_NAME_SNAKE$', snake_case($relationName), $detailTabGridTemp) . PHP_EOL;

                    }
                    // 如果是belongToOne, 则在grid中添加批量归属按钮
                    if ($relation->type == 'mt1') {
                        $relationName        = $relation->inputs[0];
                        $batchBelongTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.batch_belong', $this->baseTemplateType);
                        $batchBelongTemplate = str_replace('$RELATION_NAME$', $relationName, $batchBelongTemplate);
                    }
                    // 如果是belongToMany, 在grid中添加批量添加按钮
                    if ($relation->type == 'mtm') {
                        // 获取详情按钮的模板数据
                        $detailButtonTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.detail_button', $this->baseTemplateType);
                        if ($flag) {
                            // 如果有一对多, 或者多对多, 获取详情按钮点进去的视图模板
                            $detailTab = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab', $this->baseTemplateType);
                            $flag      = false;
                        }
                        $relationName = $relation->inputs[0];
                        // 获取里面tab项的内容
                        $detailTabItemTemp = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab_item', $this->baseTemplateType);
                        $detailTabItemTemp = str_replace('$RELATION_NAME$', $relationName, $detailTabItemTemp);
                        $detailTabItem     .= str_replace('$RELATION_NAME_CAMEL$', camel_case($relationName), $detailTabItemTemp) . PHP_EOL;
                        // 获取tab_content里的内容
                        $detailTabGridTemp = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.tab_item_content', $this->baseTemplateType);
                        $detailTabGridTemp = str_replace('$RELATION_NAME$', $relationName, $detailTabGridTemp);
                        $detailTabGridTemp = str_replace('$RELATION_NAME_CAMEL$', camel_case($relationName), $detailTabGridTemp);
                        $detailTabGrid     .= str_replace('$RELATION_NAME_SNAKE$', snake_case($relationName), $detailTabGridTemp) . PHP_EOL;
                    }

                    // 如果是多态morphTo关系, 在grid中添加批量编辑按钮
                    if ($relation->type == 'mht') {
                        $relationName      = $relation->inputs[0];
                        $batchEditTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.relation-edit.batch_edit', $this->baseTemplateType);
                        $batchEditTemplate = str_replace('$RELATION_NAME$', $relationName, $batchEditTemplate);
                    }
                }
                if (!empty($detailTabItem)) {
                    $detailTab = str_replace('$TAB_ITEMS$', $detailTabItem, $detailTab);
                }
                $templateData = str_replace('$DETAIL_BUTTON$', $detailButtonTemplate, $templateData);
                $templateData = str_replace('$BATCH_BELONG$', $batchBelongTemplate, $templateData);
                $templateData = str_replace('$BATCH_EDIT$', $batchEditTemplate, $templateData);
                $templateData = str_replace('$DETAIL_TAB$', $detailTab, $templateData);
                $templateData = str_replace('$DETAIL_TAB_GRID$', $detailTabGrid, $templateData);

                $templateData = fill_template($this->commandData->dynamicVars, $templateData);
                /**
                 * relations crud end
                 */

                /**
                 * upload image store and update start
                 */
                foreach ($this->commandData->fields as $key => $field) {
                    $imgFlag = false;
                    // 控件类型
                    if (!empty($field->htmlType)) {
                        $strHtmlType = $field->htmlType;
                    }
                    // 如果控件类型不是image,则跳过
                    if ($strHtmlType == 'image') {
                        $imgFlag = true;
                    }
                    // 判断是否有name字段
                    $flag = false;
                    // name
                    $name = '';
                    // label
                    $label = '';
                    // name
                    if (!empty($field->name)) {
                        $name  = $field->name;
                        $label = $field->name;
                        $flag  = true;
                    }
                    // label
                    if (!empty($field->label)) {
                        $label = $field->label;
                    }
                    // 增加图片上传的 `store`, `update`方法
                    if ($imgFlag) {
                        // 新增
                        $strUploadImageStore = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.upload-image.store', $this->baseTemplateType);
                        $strUploadImageStore = fill_template($this->commandData->dynamicVars, $strUploadImageStore);
                        // 替换字段【图片大小、路径、缩略图尺寸】
                        $strUploadImageStore = str_replace('$IMAGE_COLUMN$', $field->name, $strUploadImageStore);
                        $strUploadImageStore = str_replace('$MAX_SIZE$', $field->maxSize, $strUploadImageStore);
                        $strUploadImageStore = str_replace('$ROOT_DIR$', $field->rootDir, $strUploadImageStore);
                        $strUploadImageStore = str_replace('$WIDTH$', $field->imgWidth, $strUploadImageStore);
                        $strUploadImageStore = str_replace('$HEIGHT$', $field->imgHeight, $strUploadImageStore);
                        $strUploadImageStore = str_replace('$ALLOWED_EXTENSIONS$', $field->allowedExtensions, $strUploadImageStore);
                        // 编辑
                        $strUploadImageUpdate = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.upload-image.update', $this->baseTemplateType);
                        $strUploadImageUpdate = fill_template($this->commandData->dynamicVars, $strUploadImageUpdate);
                        $updateRules          = new RequestGenerator($this->commandData);
                        $strUploadImageUpdate = str_replace('$UPDATE_RULES$', implode(',' . infy_nl_tab(1, 2), $updateRules->generateUpdateRules()), $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$IMAGE_COLUMN$', $field->name, $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$MAX_SIZE$', $field->maxSize, $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$ROOT_DIR$', $field->rootDir, $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$WIDTH$', $field->imgWidth, $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$HEIGHT$', $field->imgHeight, $strUploadImageUpdate);
                        $strUploadImageUpdate = str_replace('$ALLOWED_EXTENSIONS$', $field->allowedExtensions, $strUploadImageUpdate);
                    }
                }
                // dd($strUploadImageStore);
                $templateData = str_replace('$LARAVEL_ADMIN_STORE_UPLOAD_IMAGE$', $strUploadImageStore, $templateData);
                $templateData = str_replace('$LARAVEL_ADMIN_UPDATE_UPLOAD_IMAGE$', $strUploadImageUpdate, $templateData);
                $templateData = fill_template($this->commandData->dynamicVars, $templateData);
                /**
                 * upload image store and update end
                 */

                /**
                 * form start
                 */
                // `laravel-admin` 的 `form` 里面的字段, 遍历 `普通字段` 和 `m2m` 关联字段
                // 按照fields.json文件中tableOrder进行排序,
                $formFields = array_merge($this->commandData->fields, $this->commandData->hasM2mRelationFields);
                $formFields = collect($this->commandData->fields)->sortBy('tableOrder');
                foreach ($formFields as $key => $field) {
                    // 如果不需要在 `form` 显示, 则跳过
                    if (!$field->inForm) {
                        continue;
                    }

                    // 判断是否有name字段
                    $flag = false;
                    // 控件类型, 默认 `text`
                    $strHtmlType = 'text';
                    // name
                    $name = '';
                    // label
                    $label = '';
                    // 控件类型
                    if (!empty($field->htmlType)) {
                        $strHtmlType = $field->htmlType;
                    }
                    // name
                    if (!empty($field->name)) {
                        $name  = $field->name;
                        $label = $field->name;
                        $flag  = true;
                    }
                    // label
                    if (!empty($field->label)) {
                        $label = $field->label;
                    }

                    // 用来记录 `选项值` 是 `模型->pluck()` 获得还是通过 `json中的options参数` 获得的标志
                    $optionsFlag = false;
                    // 循环获取 `displayField` 中指定 `模型名` 所要取得的 `字段` 数据
                    if (count($field->displayField) > 0) {
                        // 用来记录 `选项值` 是 `模型->pluck()` 获得还是通过 `json中的options参数` 获得的标志
                        $optionsFlag = true;
                        // 数据的变量名, `str_plural` 函数把字符串转换成复数形式
                        $optionsFieldOptions = str_finish(camel_case($field->displayField[0]), 's');
                        // 模型名
                        $optionsModelName = $field->displayField[0];
                        // 键[key]
                        $optionsKey = $field->displayField[1];
                        // 值[value]
                        $optionsValue = $field->displayField[2];
                        // 拼接查询数据库需要的模板
                        $strModelDisplay    = str_replace('$FIELD_OPTIONS$', $optionsFieldOptions, $strFormGetOptionsTemplate);
                        $strModelDisplay    = str_replace('$MODEL_NAME$', $optionsModelName, $strModelDisplay);
                        $strModelDisplay    = str_replace('$KEY$', $optionsKey, $strModelDisplay);
                        $strModelDisplay    = str_replace('$VALUE$', $optionsValue, $strModelDisplay) . yunjuji_nl();
                        $strOptionTemplates = str_replace('$OPTIONS$', '$' . $optionsFieldOptions, $strFormOptionTemplate);
                        $strFromType        = $strHtmlType . '(\'' . $name . '\', \'' . $label . '\')' . $strOptionTemplates;
                        $strLaravelAdminFieldList .= $strModelDisplay;
                        // 如果字段的 `options` 需要用到模型, 则需要在命名空间中引入该模型
                        if (!in_array($optionsModelName, $namespaceModelList)) {
                            $namespaceModelList[] = $this->commandData->namespaceModelMapping[$optionsModelName];
                        }
                    } else {
                        $strFromType = $strHtmlType . '(\'' . $name . '\', \'' . $label . '\')';
                        // 需要三个参数的字段
                        if (in_array($field->htmlType, array('timeRange', 'map', 'dateRange', 'datetimeRange'))) {
                            $second_field_name = $field->htmlType . 'Second';
                            if (!empty($field->options['second_field_name'])) {
                                $second_field_name = $field->options['second_field_name'];
                            }
                            $strFromType = $strHtmlType . '(\'' . $name . '\', \'' . $second_field_name . '\', \'' . $label . '\')';
                        }
                        // 图片字段
                        if ($field->htmlType == 'image') {
                            $strFromType = $strHtmlType . '(\'' . $name . '\', \'' . $label . '\')->removable()';
                            // 如果是图片字段 需要用到模型, 则需要在命名空间引入图片上传类
                            // $namespaceModelList[] = $this->commandData->namespaceModelMapping['UploadImage'];
                            // $namespaceModelList[] = $this->commandData->namespaceModelMapping['Validator'];
                            $namespaceModelList[] = 'App\Classes\Image\UploadImage';
                            $namespaceModelList[] = 'Validator';
                        }
                    }
                    // 获取 `options` 中的属性和值, 略过 `second_field_name` 和 `options` 参数
                    if ($flag) {
                        if (!empty($field->options)) {
                            foreach ($field->options as $key => $value) {
                                // 需要三个参数的字段
                                if ($key == 'second_field_name') {
                                    continue;
                                }

                                // 做一个判断, 如果选项值已经读取数据库的了, 就略过这个选项
                                if ($optionsFlag && $key === "options") {
                                    continue;
                                }

                                // 值如果是数组, 则转换为数组
                                if (is_array($value)) {
                                    $value = var_export($value, true);
                                    $strFromType .= '->' . $key . '(' . $value . ')';
                                } else {
                                    $strFromType .= '->' . $key . '(\'' . $value . '\')';
                                }
                            }
                        }
                    }
                    $strLaravelAdminFieldList .= str_replace('$LARAVEL_ADMIN_FORM_OPTIONS$', $strFromType, $strFormFieldTemplate . "\n");
                }

                // `laravel-admin` 的 `form` 中字段列表
                $templateData = str_replace('$LARAVELADMIN_FIELD_LIST$', $strLaravelAdminFieldList, $templateData);
                /**
                 * form end
                 */
                
                /**
                 * grid start
                 */
                $detailViewItems = '';
                $detailItems     = '';
                $batchEditInputs = '';
                // `laravel-admin` 的 `grid` 里面的字段, 遍历普通字段和m2m关联字段
                $fields = array_merge($this->commandData->fields, $this->commandData->hasM2mRelationFields);
                // 按照字段中的tableOrder进行一个排序
                $fields = collect($fields)->sortBy('tableOrder');
                foreach ($fields as $key => $field) {
                    if (!$field->inIndex) {
                        continue;
                    }
                    // 判断是否有 `name` 字段
                    $flag = false;
                    // 是 `grid` 里面的 `column` 是 `多对多关联关系` 还是 `普通模式`
                    $columnFlag = false;
                    // name
                    $name = '';
                    // title
                    $title = '';
                    // name
                    if (!empty($field->name)) {
                        $flag = true;
                        $name  = $field->name;
                        $title = $field->name;
                    }
                    // title
                    if (!empty($field->title)) {
                        $title = $field->title;
                    }

                    // 单列模板替换
                    if ($flag) {
                        // `mtm` 关联关系
                        if (array_key_exists($name, $this->commandData->hasRelationFields) && isset($this->commandData->hasRelationFields[$name]['type']) && $this->commandData->hasRelationFields[$name]['type'] === 'mtm') {
                            // 需要显示的 `别名` 字段
                            $displayFieldName = 'id';
                            if (count($field->displayField) > 0) {
                                $displayFieldName = $field->displayField[2];
                            }
                            $tempGridColumnData = str_replace('$MODEL_RELATION$', $name, $strGridMtmColumnTemplate);
                            $tempGridColumnData = str_replace('$FIELD_TITLE$', $title, $tempGridColumnData);
                            $tempGridColumnData = str_replace('$DIDPLAY_FIELD_NAME$', $displayFieldName, $tempGridColumnData . "\n");
                        } else {
                            // 需要显示的 `别名` 字段
                            if (count($field->displayField) > 0) {
                                $name = camel_case($field->displayField[0]) . '.' . $field->displayField[2];
                            }
                            // 图片字段
                            if ($field->htmlType == 'image') {
                                $tempGridColumnData = str_replace('$FIELD_NAME$', $name, $strGridImgColumnTemplate);
                                $tempGridColumnData = str_replace('$FIELD_TITLE$', $title, $tempGridColumnData);
                            } else {
                                $tempGridColumnData = str_replace('$FIELD_NAME$', $name, $strGridColumnTemplate);
                                $tempGridColumnData = str_replace('$FIELD_TITLE$', $title, $tempGridColumnData);
                            }
                        }
                        // 判断字段是否需要使用标签的形式展示
                        if (empty($field->labelOptions)) {
                            // 不需要使用标签展示
                            $tempGridColumnData = str_replace('$IS_DISPLAY_LABEL$', '', $tempGridColumnData);
                        } else {
                            // 需要使用标签展示
                            $labelOptions = explode(',', $field->labelOptions);
                            $option       = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.column_display_label', $this->baseTemplateType);
                            $options      = '';
                            // Log::info('test', $labelOptions);
                            foreach ($labelOptions as $labelOption) {
                                $optionArr = explode(':', $labelOption);
                                $temp      = str_replace('$OPTION$', $optionArr[0], $option);
                                // 0大多数是表示否定的意思, 这里背景色改成红色
                                if ($optionArr[0] == 0) {
                                    $temp = str_replace('bg-green', 'bg-red', $temp);
                                }
                                $temp    = str_replace('$OPTION_VALUE$', $optionArr[1], $temp);
                                $options .= $temp;
                            }
                            $columnDisplay = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.column_display', $this->baseTemplateType);
                            $columnDisplay = str_replace('$OPTIONS$', $options, $columnDisplay);
                            // Log::info($columnDisplay, $tempGridColumnData);
                            $tempGridColumnData = str_replace('$IS_DISPLAY_LABEL$', $columnDisplay, $tempGridColumnData);
                        }
                        // 判断字段是否在页内详情中显示
                        if (!empty($field->isDisplayPageDetail)) {
                            // 需要在页面详情中展示
                            // 视图中的item
                            $detailViewItem  = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.detail_view_item', $this->baseTemplateType);
                            $detailViewItem  = str_replace('$FIELD$', $field->name, $detailViewItem);
                            $detailViewItems .= str_replace('$FIELD_TITLE$', $field->title, $detailViewItem);
                        }
                        // 判断字段是否放需要行内编辑
                        if (empty($field->rowEdit)) {
                            // 不需要行内编辑
                            $tempGridColumnData = str_replace('$IS_ROW_EDIT$', '', $tempGridColumnData . PHP_EOL);
                        } else {
                            // 需要行内编辑
                            $tempGridColumnData = str_replace('$IS_ROW_EDIT$', '->editable()', $tempGridColumnData . PHP_EOL);
                            $templateData       = str_replace('$IS_ROW_EDIT$', '', $templateData);
                        }
                        $batchEditInput  = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.batch-edit.batch_edit_input_view', $this->baseTemplateType);
                        $batchEditInput  = str_replace('$FIELD$', $field->name, $batchEditInput);
                        $batchEditInput  = str_replace('$FIELD_TITLE$', $field->title, $batchEditInput);
                        $batchEditInputs .= $batchEditInput;

                        $strLaravelAdminColumnList .= $tempGridColumnData;
                    }
                }
                // 是否有行内按钮
                // 获取fields.json文件的绝对路径
                $jsonPath = $this->commandData->config->options['fieldsFile'];
                $jsonPath = dirname($jsonPath);
                // 如果button.json文件存在, 说明有行内按钮
                // Log::info($jsonPath . '/button.json');
                if (is_file($jsonPath . '/button.json')) {
                    $buttonJsonContent  = file_get_contents($jsonPath . '/button.json');
                    $buttonJsons        = json_decode($buttonJsonContent);
                    $buttonTemplateData = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.button', $this->baseTemplateType);
                    $buttons            = '';
                    foreach ($buttonJsons as $buttonJson) {
                        $buttons .= str_replace('$BUTTON_TITLE$', $buttonJson->name, $buttonTemplateData);
                    }
                    $templateData = str_replace('$ROW_BUTTON$', $buttons, $templateData);
                }
                // 如果不需要行内按钮
                $templateData = str_replace('$ROW_BUTTON$', '', $templateData);
                // 获取生成项目的根路径
                $generatePath = $this->commandData->config->options['generatePath'];
                // 拼装视图存放的相对路径
                $relativeViewPath = $this->commandData->dynamicVars['$VIEW_PREFIX$'] . $this->commandData->dynamicVars['$MODEL_NAME_PLURAL_SNAKE$'];
                $relativeViewPath = str_replace('\\', '.', $relativeViewPath);
                // 获取视图存放的绝对路径
                $absoluteViewPath = $this->commandData->config->pathViews;
                // 生成批量编辑按钮
                $batchEditButton = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.batch-edit.batch_edit_button', $this->baseTemplateType);
                $batchEditButton = str_replace('$BATCH_EDIT_VIEW_PATH$', $relativeViewPath . '.batch_edit', $batchEditButton);
                $templateData    = str_replace('$BATCH_EDIT_BUTTON$', $batchEditButton, $templateData);
                $batchEditCode   = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.batch-edit.update', $this->baseTemplateType);
                $templateData    = str_replace('$BATCH_EDIT_CODE$', $batchEditCode, $templateData);
                // 生成批量编辑的视图文件
                // Log::info($viewPath);
                $batchEditView = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.custom.batch-edit.batch_edit_view', $this->baseTemplateType);
                $batchEditView = str_replace('$INPUTS$', $batchEditInputs, $batchEditView);
                $batchEditView = fill_template($this->commandData->dynamicVars, $batchEditView);
                FileUtil::createFile($absoluteViewPath, 'batch_edit.blade.php', $batchEditView);
                // 如果不需要行内编辑
                $templateData = str_replace('$IS_ROW_EDIT$', '$MODEL_NAME$', $templateData);
                // 判断有无页内详情视图内容, 生成详情对应的视图文件
                if (!empty($detailViewItems)) {
                    // 视图
                    $detailView   = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.detail_view', $this->baseTemplateType);
                    $detailView   = str_replace('$VIEW_ITEMS$', $detailViewItems, $detailView);
                    FileUtil::createFile($absoluteViewPath, 'page_detail.blade.php', $detailView);
                }
                // 判断有无页内详情字段显示, 将控制器中的变量替换为相应的代码
                if (!empty($detailView)) {
                    $detail       = yunjuji_get_template($this->formModePrefix . 'scaffold.controller.grid.detail', $this->baseTemplateType);
                    $detail       = str_replace('$DETAIL_VIEW_PATH$', $relativeViewPath . '.page_detail', $detail);
                    $templateData = str_replace('$IS_DISPLAY_PAGE_DETAIL$', $detail, $templateData);
                }
                // 如果不需要页内详情
                $templateData = str_replace('$IS_DISPLAY_PAGE_DETAIL$', '', $templateData);

                // 替换 `grid` 中的 `column` 模板
                $templateData = str_replace('$LARAVELADMIN_COLUMN_LIST$', $strLaravelAdminColumnList, $templateData);
                /**
                 * grid end
                 */

                /**
                 * filter start
                 */
                // 过滤字段
                if (count($this->commandData->filterFields) > 0) {
                    // 遍历过滤字段
                    foreach ($this->commandData->filterFields as $filterField) {
                        // name
                        $filterName = '';
                        // label, 默认 `name` 值
                        $filterLabel = '';
                        // 操作符, 默认 `like`
                        $filterOperator = 'like';
                        // 控件
                        $filterHtmlType = '';

                        // name
                        if (!empty($filterField->name)) {
                            $filterName = $filterField->name;
                            $filterLabel = $filterField->name;
                        }
                        // label
                        if (!empty($filterField->label)) {
                            $filterLabel = $filterField->label;
                        }
                        // 操作符
                        if (!empty($filterField->operator)) {
                            $filterOperator = $filterField->operator;
                        }
                        // 控件
                        if (!empty($filterField->htmlType)) {
                            $filterHtmlType = $filterField->htmlType;
                        }

                        // 如果某些 `过滤控件类型(htmlType)` 也需要 选项值`, 直接将 `过滤控件类型(htmlType)` 加进数组即可
                        if (in_array($filterHtmlType, array('select', 'multipleSelect'))) {
                            foreach (array_merge($this->commandData->fields, $this->commandData->hasM2mRelationFields) as $field) {
                                // 判断字段名跟过滤字段名是否一致
                                if ($field->name == $filterName) {
                                    // 在 `displayField` 有值的情况下进行
                                    if (count($field->displayField) > 0) {
                                        // 数据的变量名, `str_plural` 函数把字符串转换成复数形式
                                        $optionsFieldOptions = str_finish(camel_case($field->displayField[0]), 's');
                                        // 模型名
                                        $optionsModelName = $field->displayField[0];
                                        // 键[key]
                                        $optionsKey = $field->displayField[1];
                                        // 值[value]
                                        $optionsValue = $field->displayField[2];
                                        // 拼接查询数据库需要的模板
                                        $strFilterGetOptions   = str_replace('$FIELD_OPTIONS$', $optionsFieldOptions, $strFilterGetOptionsTemplate);
                                        $strFilterGetOptions    = str_replace('$MODEL_NAME$', $optionsModelName, $strFilterGetOptions);
                                        $strFilterGetOptions    = str_replace('$KEY$', $optionsKey, $strFilterGetOptions);
                                        $strFilterGetOptions    = str_replace('$VALUE$', $optionsValue, $strFilterGetOptions) . yunjuji_nl();

                                        if ($filterName) {
                                            $strLaravelAdminFilterFieldList .= yunjuji_tab(4*4) . $strFilterGetOptions;
                                            $strLaravelAdminFilterFieldList .= yunjuji_tab(4*4) . '$filter->' . $filterOperator . '(\'' . $filterName . '\', \''. $filterLabel . '\')';
                                            if ($filterHtmlType) {
                                                $strLaravelAdminFilterFieldList .= '->' . $filterHtmlType . '($' . $optionsFieldOptions . ')';
                                            }
                                            $strLaravelAdminFilterFieldList .= ';' . yunjuji_nl();
                                        }
                                    } else {
                                        // 有 `options` 参数时所做的操作
                                        $filterFlag = false;
                                        $options = 'array()';
                                        if ( !empty($field->options) && isset($field->options['options']) ) {
                                            $options = var_export($field->options['options'], true);
                                        }
                                        $strFilterGetOptions = '$options = '.$options;
                                        $strFilterGetOptions .= ';';
                                        if ($filterName) {
                                            $strLaravelAdminFilterFieldList .= yunjuji_tab(4*4) . $strFilterGetOptions;
                                            $strLaravelAdminFilterFieldList .= yunjuji_tab(4*4) . '$filter->' . $filterOperator . '(\'' . $filterName . '\', \''. $filterLabel . '\')';
                                            if ($filterHtmlType) {
                                                $strLaravelAdminFilterFieldList .= '->' . $filterHtmlType . '($options)';
                                            }
                                            $strLaravelAdminFilterFieldList .= ';' . yunjuji_nl();
                                        }
                                    }
                                } else {
                                    // 没有选项, 要做异常处理
                                }
                            }
                        } else {
                            if ($filterName) {
                                $strLaravelAdminFilterFieldList .= yunjuji_tab(4*4) . '$filter->' . $filterOperator . '(\'' . $filterName . '\', \''. $filterLabel . '\')';
                                if ($filterHtmlType) {
                                    $strLaravelAdminFilterFieldList .= '->' . $filterHtmlType . '()';
                                }
                                $strLaravelAdminFilterFieldList .= ';' . yunjuji_nl();
                            }
                        }
                    }
                }

                // 替换 `filter`
                $templateData = str_replace('$LARAVELADMIN_FILTER_FIELD_LIST$', $strLaravelAdminFilterFieldList, $templateData);
                /**
                 * filter end
                 */

                /**
                 * namespace start
                 */
                // 拼装 `模型命名空间`
                $aNamespaceModelList = [];
                foreach ($namespaceModelList as $key => $value) {
                    $aNamespaceModelList[] = 'use ' . $value . ';';
                }

                // 替换 `模型命名空间`
                $templateData = str_replace('$USE_NAMESPACE_MODEL_LIST$', implode(yunjuji_nl(), $aNamespaceModelList), $templateData);
                /**
                 * namespace end
                 */
            }
        }
        /**
         * tian add end
         */

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        // `linux` 和 `win` 有区别
        if (DIRECTORY_SEPARATOR != '\\') {
            FileUtil::createFile(str_replace('\\', DIRECTORY_SEPARATOR, $this->path), $this->fileName, $templateData);
        } else {
            FileUtil::createFile($this->path, $this->fileName, $templateData);
        }

        $this->commandData->commandComment("\nController created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    /**
     * [generateDataTable 【产生datatables】]
     * @return [type] [description]
     */
    private function generateDataTable()
    {
        $templateData = yunjuji_get_template($this->formModePrefix . 'scaffold.datatable', $this->baseTemplateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $headerFieldTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.views.datatable_column', $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }
            $headerFields[] = $fieldTemplate = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );
        }

        $path = $this->commandData->config->pathDataTables;

        $fileName = $this->commandData->modelName . 'DataTable.php';

        $fields = implode(',' . infy_nl_tab(1, 3), $headerFields);

        $templateData = str_replace('$DATATABLE_COLUMNS$', $fields, $templateData);

        FileUtil::createFile($path, $fileName, $templateData);

        $this->commandData->commandComment("\nDataTable created: ");
        $this->commandData->commandInfo($fileName);
    }

    /**
     * [rollback 【回退】]
     * @return [type] [description]
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Controller file deleted: ' . $this->fileName);
        }

        /**
         * tian comment start
         */
        // if ($this->commandData->getAddOn('datatables')) {
        //     if ($this->rollbackFile($this->commandData->config->pathDataTables, $this->commandData->modelName.'DataTable.php')) {
        //         $this->commandData->commandComment('DataTable file deleted: '.$this->fileName);
        //     }
        // }
        /**
         * tian comment end
         */
    }
}
