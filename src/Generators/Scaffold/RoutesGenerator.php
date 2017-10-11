<?php

namespace Yunjuji\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
// tian add
use InfyOm\Generator\Utils\FileUtil;

class RoutesGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $routeContents;

    /** @var string */
    private $routesTemplate;

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
        /**
         * tian comment start
         */
        // $this->commandData = $commandData;
        // $this->path = $commandData->config->pathRoutes;
        // $this->routeContents = file_get_contents($this->path);
        // if (!empty($this->commandData->config->prefixes['route'])) {
        //     $this->routesTemplate = get_template('scaffold.routes.prefix_routes', 'laravel-generator');
        // } else {
        //     $this->routesTemplate = get_template('scaffold.routes.routes', 'laravel-generator');
        // }
        // $this->routesTemplate = fill_template($this->commandData->dynamicVars, $this->routesTemplate);
        /**
         * tian comment end
         */

        /**
         * tian add start
         */
        $this->commandData      = $commandData;
        $this->path             = $commandData->config->pathRoutes;
        
        $this->routeContents    = file_get_contents($this->path);
        $this->baseTemplateType = config('yunjuji.generator.templates.base', 'yunjuji-generator');
        if ($this->commandData->getOption('formMode')) {
            $this->formMode       = $this->commandData->getOption('formMode');
            $this->formModePrefix = $this->formMode . '.';
        }
        if (!empty($this->commandData->config->prefixes['route'])) {
            $this->routesTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.routes.prefix_routes', $this->baseTemplateType);
        } else {
            $this->routesTemplate = yunjuji_get_template($this->formModePrefix . 'scaffold.routes.routes', $this->baseTemplateType);
        }
        $this->routesTemplate = yunjuji_fill_template($this->commandData->dynamicVars, $this->routesTemplate);
        /**
         * tian add end
         */
    }

    /**
     * [generate 产生]
     * @return [type] [description]
     */
    public function generate()
    {
        /**
         * tian add start
         */
        // 【在web目录下创建相应的路由文件】
        $routePath           = dirname($this->path);
        $this->routeContents = "<?php\n\n" . $this->routesTemplate;
        // `linux` 和 `win` 有区别
        if (DIRECTORY_SEPARATOR != '\\') {
            FileUtil::createFile($routePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $this->commandData->config->prefixes['route']) . DIRECTORY_SEPARATOR, $this->commandData->config->mSnakePlural . '.php', $this->routeContents);
        } else {
            // mNam是全大写, mSnakePlural蛇形有s
            FileUtil::createFile($routePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $this->commandData->config->prefixes['route'] . DIRECTORY_SEPARATOR, $this->commandData->config->mSnakePlural . '.php', $this->routeContents);
        }
        /**
         * tian add end
         */

        /**
         * tian comment start
         */
        // $this->routeContents .= "\n\n".$this->routesTemplate;

        // file_put_contents($this->path, $this->routeContents);
        // $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
        /**
         * tian comment end
         */
    }

    /**
     * [rollback 回退函数]
     * @return [type] [description]
     */
    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }

        /**
         * tian add start
         */
        // 删除路由文件
        if ($this->rollbackFile(dirname($this->path), DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $this->commandData->config->prefixes['route'] . DIRECTORY_SEPARATOR . $this->commandData->config->mSnakePlural . '.php')) {
            $this->commandData->commandComment('scaffold routes deleted');
        }
        /**
         * tian add end
         */
    }

    /**
     * tian add
     * [rollbackFile 删除路由文件]
     * @param  [type] $path     [description]
     * @param  [type] $fileName [description]
     * @return [type]           [description]
     */
    public function rollbackFile($path, $fileName)
    {
        if (file_exists($path . $fileName)) {
            return FileUtil::deleteFile($path, $fileName);
        }

        return false;
    }
}
