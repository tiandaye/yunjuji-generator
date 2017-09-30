<?php

namespace Yunjuji\Generator\Common;

use Illuminate\Support\Str;

class GeneratorConfig
{
    /* Namespace variables */
    public $nsApp;
    public $nsRepository;
    public $nsModel;
    public $nsDataTables;
    public $nsModelExtend;

    public $nsApiController;
    public $nsApiRequest;

    public $nsRequest;
    public $nsRequestBase;
    public $nsController;
    public $nsBaseController;

    /* Path variables */
    public $pathRepository;
    public $pathModel;
    public $pathDataTables;

    public $pathApiController;
    public $pathApiRequest;
    public $pathApiRoutes;
    public $pathApiTests;
    public $pathApiTestTraits;

    public $pathController;
    public $pathRequest;
    public $pathRoutes;
    public $pathViews;
    public $modelJsPath;

    /* Model Names */
    public $mName;
    public $mPlural;
    public $mCamel;
    public $mCamelPlural;
    public $mSnake;
    public $mSnakePlural;
    public $mDashed;
    public $mDashedPlural;
    public $mSlash;
    public $mSlashPlural;
    public $mHuman;
    public $mHumanPlural;

    public $forceMigrate;

    /* Generator Options */
    public $options;

    /* Prefixes */
    public $prefixes;

    /* Command Options */
    public static $availableOptions = [
        /**
         * tian add start
         */
        // 是否需要rbac的参数【现在该参数没用】
        'rbac',
        // form表单模式变量【默认-datatable方式, laravel-admin方式(目前重点), laravel-backpack方式】
        'formMode',
        // 过滤文件【过滤区域的字段信息】
        'filterFieldsFile',
        // 模型的命名空间【解决模型不在一个命名空间下】
        'namespaceModelMappingFile',
        // 生成的路径
        'generatePath',
        /**
         * tian add end
         */

        'fieldsFile',
        'jsonFromGUI',
        'tableName',
        'fromTable',
        'save',
        'primary',
        'prefix',
        'paginate',
        'skip',
        'datatables',
        'views',
        'relations',
    ];

    public $tableName;

    /** @var string */
    protected $primaryName;

    /* Generator AddOns */
    public $addOns;

    /**
     * [init 【初始化】]
     * @param  CommandData &$commandData [description]
     * @param  [type]      $options      [description]
     * @return [type]                    [description]
     */
    public function init(CommandData &$commandData, $options = null)
    {
        // dd('GeneratorConfig');
        if (!empty($options)) {
            self::$availableOptions = $options;
        }

        $this->mName = $commandData->modelName;

        // 准备(预处理)插件, 插件是否启用
        $this->prepareAddOns();
        // 准备选项
        $this->prepareOptions($commandData);
        // 准备模型名
        $this->prepareModelNames();
        // 准备前缀
        $this->preparePrefixes();
        // 加载路径
        $this->loadPaths($commandData);
        // $this->loadPaths();
        // 准备表名
        $this->prepareTableName();
        // 准备主键名
        $this->preparePrimaryName();
        // 加载命名空间
        $this->loadNamespaces($commandData);
        // 加载动态变量, 将$$这些变量给替换为真实的值
        $commandData = $this->loadDynamicVariables($commandData);
    }

    /**
     * [loadNamespaces 【加载命名空间】]
     * @param  CommandData &$commandData [description]
     * @return [type]                    [description]
     */
    public function loadNamespaces(CommandData &$commandData)
    {
        $prefix = $this->prefixes['ns'];

        if (!empty($prefix)) {
            $prefix = '\\' . $prefix;
        }

        $this->nsApp        = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsApp        = substr($this->nsApp, 0, strlen($this->nsApp) - 1);
        $this->nsRepository = config('infyom.laravel_generator.namespace.repository', 'App\Repositories') . $prefix;
        $this->nsModel      = config('infyom.laravel_generator.namespace.model', 'App\Models') . $prefix;
        if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            $this->nsModel = config('infyom.laravel_generator.namespace.model', 'App\Models');
        }
        $this->nsDataTables  = config('infyom.laravel_generator.namespace.datatables', 'App\DataTables') . $prefix;
        $this->nsModelExtend = config(
            'infyom.laravel_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $this->nsApiController = config(
            'infyom.laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ) . $prefix;
        $this->nsApiRequest = config('infyom.laravel_generator.namespace.api_request', 'App\Http\Requests\API') . $prefix;

        $this->nsRequest        = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests') . $prefix;
        $this->nsRequestBase    = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests');
        $this->nsBaseController = config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers');
        $this->nsController     = config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers') . $prefix;
    }

    /**
     * [loadPaths 【加载路径】]
     * @return [type] [description]
     */
    public function loadPaths($commandData)
    {
        $prefix = $this->prefixes['path'];

        if (!empty($prefix)) {
            $prefix .= '/';
        }

        $viewPrefix = $this->prefixes['view'];

        if (!empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        /**
         * tian add start
         */
        if (!empty($commandData->getOption('generatePath'))) {
            $generatePath = $commandData->getOption('generatePath');
            $this->pathRepository = $generatePath . '/' . 'app/' . 'Repositories/' . $prefix;

            $this->pathModel = $generatePath . '/' . 'app/' . 'Models/' . $prefix;
            // if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            //     $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/'));
            // }

            $this->pathDataTables = $generatePath . '/' . 'app/' . 'DataTables/' . $prefix;

            $this->pathApiController = $generatePath . '/' . 'app/' . 'Http/Controllers/API/' . $prefix;

            $this->pathApiRequest = $generatePath . '/' . 'app/' . 'Http/Requests/API/' . $prefix;

            $this->pathApiRoutes = $generatePath . '/' . 'routes/api.php';

            $this->pathApiTests = $generatePath . '/' . 'tests/';

            $this->pathApiTestTraits = $generatePath . '/' . 'tests/traits/';

            $this->pathController = $generatePath . '/' . 'app/' . 'Http/Controllers/' . $prefix;

            $this->pathRequest = $generatePath . '/' . 'app/' . 'Http/Requests/' . $prefix;

            $this->pathRoutes = $generatePath . '/' . 'routes/web.php';

            $this->pathViews = $generatePath . '/' . 'resources/views/' . $viewPrefix . $this->mSnakePlural . '/';

            $this->modelJsPath = $generatePath . '/' . 'resources/assets/js/models/';
        } else {
            $this->pathRepository = config(
                'infyom.laravel_generator.path.repository',
                app_path('Repositories/')
            ) . $prefix;

            $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/')) . $prefix;
            if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
                $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/'));
            }

            $this->pathDataTables = config('infyom.laravel_generator.path.datatables', app_path('DataTables/')) . $prefix;

            $this->pathApiController = config(
                'infyom.laravel_generator.path.api_controller',
                app_path('Http/Controllers/API/')
            ) . $prefix;

            $this->pathApiRequest = config(
                'infyom.laravel_generator.path.api_request',
                app_path('Http/Requests/API/')
            ) . $prefix;

            $this->pathApiRoutes = config('infyom.laravel_generator.path.api_routes', app_path('Http/api_routes.php'));

            $this->pathApiTests = config('infyom.laravel_generator.path.api_test', base_path('tests/'));

            $this->pathApiTestTraits = config('infyom.laravel_generator.path.test_trait', base_path('tests/traits/'));

            $this->pathController = config(
                'infyom.laravel_generator.path.controller',
                app_path('Http/Controllers/')
            ) . $prefix;

            $this->pathRequest = config('infyom.laravel_generator.path.request', app_path('Http/Requests/')) . $prefix;

            $this->pathRoutes = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));

            $this->pathViews = config(
                'infyom.laravel_generator.path.views',
                base_path('resources/views/')
            ) . $viewPrefix . $this->mSnakePlural . '/';

            $this->modelJsPath = config(
                'infyom.laravel_generator.path.modelsJs',
                base_path('resources/assets/js/models/')
            );
        }
        /**
         * tian add end
         */

        /**
         * tian add comment start
         */
        // $this->pathRepository = config(
        //     'infyom.laravel_generator.path.repository',
        //     app_path('Repositories/')
        // ) . $prefix;

        // $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/')) . $prefix;
        // if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
        //     $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/'));
        // }

        // $this->pathDataTables = config('infyom.laravel_generator.path.datatables', app_path('DataTables/')) . $prefix;

        // $this->pathApiController = config(
        //     'infyom.laravel_generator.path.api_controller',
        //     app_path('Http/Controllers/API/')
        // ) . $prefix;

        // $this->pathApiRequest = config(
        //     'infyom.laravel_generator.path.api_request',
        //     app_path('Http/Requests/API/')
        // ) . $prefix;

        // $this->pathApiRoutes = config('infyom.laravel_generator.path.api_routes', app_path('Http/api_routes.php'));

        // $this->pathApiTests = config('infyom.laravel_generator.path.api_test', base_path('tests/'));

        // $this->pathApiTestTraits = config('infyom.laravel_generator.path.test_trait', base_path('tests/traits/'));

        // $this->pathController = config(
        //     'infyom.laravel_generator.path.controller',
        //     app_path('Http/Controllers/')
        // ) . $prefix;

        // $this->pathRequest = config('infyom.laravel_generator.path.request', app_path('Http/Requests/')) . $prefix;

        // $this->pathRoutes = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));

        // $this->pathViews = config(
        //     'infyom.laravel_generator.path.views',
        //     base_path('resources/views/')
        // ) . $viewPrefix . $this->mSnakePlural . '/';

        // $this->modelJsPath = config(
        //     'infyom.laravel_generator.path.modelsJs',
        //     base_path('resources/assets/js/models/')
        // );
        /**
         * tian add comment end
         */
    }

    /**
     * [loadDynamicVariables 【加载动态变量】]
     * @param  CommandData &$commandData [description]
     * @return [type]                    [description]
     */
    public function loadDynamicVariables(CommandData &$commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->nsRepository);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_DATATABLES$', $this->nsDataTables);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->nsApiController);
        $commandData->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->nsApiRequest);

        $commandData->addDynamicVariable('$NAMESPACE_BASE_CONTROLLER$', $this->nsBaseController);
        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST$', $this->nsRequest);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->nsRequestBase);

        $commandData->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $commandData->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);
        $commandData->addDynamicVariable('$MODEL_NAME_DASHED$', $this->mDashed);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_DASHED$', $this->mDashedPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SLASH$', $this->mSlash);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SLASH$', $this->mSlashPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_HUMAN$', $this->mHuman);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_HUMAN$', $this->mHumanPlural);

        /**
         * tian add start
         */
        // 控制是否显示过滤区域的
        $commandData->addDynamicVariable('$FILTER_AREA_DISPLAY$', 'false');
        /**
         * tian add end
         */

        if (!empty($this->prefixes['route'])) {
            // tian comment
            // $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $this->prefixes['route'].'.');
            // $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $this->prefixes['route']).'/');

            /**
             * tian add start
             */
            $aRouteNamedPrefix = explode('\\', $this->prefixes['route']);
            $aRouteNamedPrefix = array_map(function ($val) {
                // 转下划线命名法
                return snake_case($val);
                // return lcfirst($val);
            }, $aRouteNamedPrefix);
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', implode('.', $aRouteNamedPrefix) . '.');
            $aRoutePrefix = explode('\\', $this->prefixes['route']);
            $aRoutePrefix = array_map(function ($val) {
                // 转下划线命名法
                return snake_case($val);
            }, $aRoutePrefix);
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', implode('/', $aRoutePrefix)) . '/');
            /**
             * tian add end
             */
        } else {
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', '');
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', '');
        }

        if (!empty($this->prefixes['ns'])) {
            $commandData->addDynamicVariable('$PATH_PREFIX$', $this->prefixes['ns'] . '\\');
        } else {
            $commandData->addDynamicVariable('$PATH_PREFIX$', '');
        }

        if (!empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $this->prefixes['view']) . '.');
        } else {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', '');
        }

        if (!empty($this->prefixes['public'])) {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', $this->prefixes['public']);
        } else {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', '');
        }

        $commandData->addDynamicVariable(
            '$API_PREFIX$',
            config('infyom.laravel_generator.api_prefix', 'api')
        );

        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('infyom.laravel_generator.api_version', 'v1')
        );

        return $commandData;
    }

    /**
     * [prepareTableName 【准备表名】]
     * @return [type] [description]
     */
    public function prepareTableName()
    {
        if ($this->getOption('tableName')) {
            $this->tableName = $this->getOption('tableName');
        } else {
            $this->tableName = $this->mSnakePlural;
        }
    }

    /**
     * [preparePrimaryName 【准备主键名】]
     * @return [type] [description]
     */
    public function preparePrimaryName()
    {
        if ($this->getOption('primary')) {
            $this->primaryName = $this->getOption('primary');
        } else {
            $this->primaryName = 'id';
        }
    }

    /**
     * [prepareModelNames 【准备模型名】]
     * @return [type] [description]
     */
    public function prepareModelNames()
    {
        $this->mPlural       = Str::plural($this->mName);
        $this->mCamel        = Str::camel($this->mName);
        $this->mCamelPlural  = Str::camel($this->mPlural);
        $this->mSnake        = Str::snake($this->mName);
        $this->mSnakePlural  = Str::snake($this->mPlural);
        $this->mDashed       = str_replace('_', '-', Str::snake($this->mSnake));
        $this->mDashedPlural = str_replace('_', '-', Str::snake($this->mSnakePlural));
        $this->mSlash        = str_replace('_', '/', Str::snake($this->mSnake));
        $this->mSlashPlural  = str_replace('_', '/', Str::snake($this->mSnakePlural));
        $this->mHuman        = title_case(str_replace('_', ' ', Str::snake($this->mSnake)));
        $this->mHumanPlural  = title_case(str_replace('_', ' ', Str::snake($this->mSnakePlural)));
    }

    /**
     * [prepareOptions 【准备选项】]
     * @param  CommandData &$commandData [description]
     * @return [type]                    [description]
     */
    public function prepareOptions(CommandData &$commandData)
    {
        foreach (self::$availableOptions as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if (isset($options['fromTable']) and $this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', false);
        if (!empty($this->options['skip'])) {
            $this->options['skip'] = array_map('trim', explode(',', $this->options['skip']));
        }

        if (!empty($this->options['datatables'])) {
            if (strtolower($this->options['datatables']) == 'true') {
                $this->addOns['datatables'] = true;
            } else {
                $this->addOns['datatables'] = false;
            }
        }
    }

    /**
     * [preparePrefixes 【准备前缀】]
     * @return [type] [description]
     */
    public function preparePrefixes()
    {
        $this->prefixes['route']  = explode('/', config('infyom.laravel_generator.prefixes.route', ''));
        $this->prefixes['path']   = explode('/', config('infyom.laravel_generator.prefixes.path', ''));
        $this->prefixes['view']   = explode('.', config('infyom.laravel_generator.prefixes.view', ''));
        $this->prefixes['public'] = explode('/', config('infyom.laravel_generator.prefixes.public', ''));

        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode(',', $this->getOption('prefix'));

            $this->prefixes['route']  = array_merge($this->prefixes['route'], $multiplePrefixes);
            $this->prefixes['path']   = array_merge($this->prefixes['path'], $multiplePrefixes);
            $this->prefixes['view']   = array_merge($this->prefixes['view'], $multiplePrefixes);
            $this->prefixes['public'] = array_merge($this->prefixes['public'], $multiplePrefixes);
        }

        $this->prefixes['route']  = array_diff($this->prefixes['route'], ['']);
        $this->prefixes['path']   = array_diff($this->prefixes['path'], ['']);
        $this->prefixes['view']   = array_diff($this->prefixes['view'], ['']);
        $this->prefixes['public'] = array_diff($this->prefixes['public'], ['']);

        $routePrefix = '';

        foreach ($this->prefixes['route'] as $singlePrefix) {
            $routePrefix .= Str::camel($singlePrefix) . '.';
        }

        if (!empty($routePrefix)) {
            $routePrefix = substr($routePrefix, 0, strlen($routePrefix) - 1);
        }

        $this->prefixes['route'] = $routePrefix;

        $nsPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            // tian comment【该函数作用: 首字母大写】
            // $nsPrefix .= Str::title($singlePrefix).'\\';

            // tian 2017/09/15 add
            $nsPrefix .= $singlePrefix . '\\';
        }

        if (!empty($nsPrefix)) {
            $nsPrefix = substr($nsPrefix, 0, strlen($nsPrefix) - 1);
        }

        $this->prefixes['ns'] = $nsPrefix;

        $pathPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            /**
             * tian comment start【该函数作用: 首字母大写】
             */
            // $pathPrefix .= Str::title($singlePrefix).'/';
            /**
             * tian comment end
             */
            // tian 2017/09/15 add
            $pathPrefix .= $singlePrefix . '/';
        }

        if (!empty($pathPrefix)) {
            $pathPrefix = substr($pathPrefix, 0, strlen($pathPrefix) - 1);
        }

        $this->prefixes['path'] = $pathPrefix;

        $viewPrefix = '';

        foreach ($this->prefixes['view'] as $singlePrefix) {
            $viewPrefix .= Str::camel($singlePrefix) . '/';
        }

        if (!empty($viewPrefix)) {
            $viewPrefix = substr($viewPrefix, 0, strlen($viewPrefix) - 1);
        }

        $this->prefixes['view'] = $viewPrefix;

        $publicPrefix = '';

        foreach ($this->prefixes['public'] as $singlePrefix) {
            $publicPrefix .= Str::camel($singlePrefix) . '/';
        }

        if (!empty($publicPrefix)) {
            $publicPrefix = substr($publicPrefix, 0, strlen($publicPrefix) - 1);
        }

        $this->prefixes['public'] = $publicPrefix;
    }

    /**
     * [overrideOptionsFromJsonFile 【重写选项来自json文件】]
     * @param  [type] $jsonData [description]
     * @return [type]           [description]
     */
    public function overrideOptionsFromJsonFile($jsonData)
    {
        $options = self::$availableOptions;

        foreach ($options as $option) {
            if (isset($jsonData['options'][$option])) {
                $this->setOption($option, $jsonData['options'][$option]);
            }
        }

        $addOns = ['swagger', 'tests', 'datatables'];

        foreach ($addOns as $addOn) {
            if (isset($jsonData['addOns'][$addOn])) {
                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
            }
        }
    }

    /**
     * [getOption 【获得选项】]
     * @param  [type] $option [description]
     * @return [type]         [description]
     */
    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    /**
     * [getAddOn 【添加】]
     * @param  [type] $addOn [description]
     * @return [type]        [description]
     */
    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    /**
     * [setOption 【设置选项】]
     * @param [type] $option [description]
     * @param [type] $value  [description]
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * [prepareAddOns 【准备插件，插件是否启用】]
     * @return [type] [description]
     */
    public function prepareAddOns()
    {
        $this->addOns['swagger']        = config('infyom.laravel_generator.add_on.swagger', false);
        $this->addOns['tests']          = config('infyom.laravel_generator.add_on.tests', false);
        $this->addOns['datatables']     = config('infyom.laravel_generator.add_on.datatables', false);
        $this->addOns['menu.enabled']   = config('infyom.laravel_generator.add_on.menu.enabled', false);
        $this->addOns['menu.menu_file'] = config('infyom.laravel_generator.add_on.menu.menu_file', 'layouts.menu');
    }
}
