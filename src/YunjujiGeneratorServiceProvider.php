<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:32:55
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-29 10:05:28
 */

namespace Yunjuji\Generator;

use Illuminate\Support\ServiceProvider;
use Yunjuji\Generator\Console\Commands\Scaffold\GenerateFieldJsonCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\GenerateCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\GenerateTestData;
use Yunjuji\Generator\Console\Commands\Scaffold\RollbackCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\PublishCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\FillDataCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\DropTableCommand;
use Yunjuji\Generator\Console\Commands\Scaffold\ScaffoldGeneratorCommand;

class YunjujiGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 可以在这做发布配置文件, 视图文件【php artisan vendor:publish】
         */
        $configPath = __DIR__ . '/../config/generator.php';

        $this->publishes([
            $configPath => config_path('yunjuji/generator.php'),
        ], 'yunjuji-generator-config');
        // php artisan vendor:publish --tag=yunjuji-generator-config --force,

        // if ($this->app->runningInConsole()) {}

        /**
         * 发布视图例子
         */
        // $this->loadViewsFrom(__DIR__.'/../../views','laraflash');
        // $this->publishes([
        //     __DIR__.'/../../views'=>base_path('resources/views/vendor/laraFlash'),
        // ]);

        // 加载路由
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // 加载migrate
        // $this->loadMigrationsFrom(__DIR__.'/path/to/migrations');

        // 加载多语言
        // $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');

        /**
         * laravel-admin
         */
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin');

        // if (file_exists($routes = admin_path('routes.php'))) {
        //     $this->loadRoutesFrom($routes);
        // }

        // if ($this->app->runningInConsole()) {
        //     $this->publishes([__DIR__.'/../config' => config_path()], 'laravel-admin-config');
        //     $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-lang');
        //     $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'laravel-admin-migrations');
        //     $this->publishes([__DIR__.'/../resources/assets' => public_path('vendor/laravel-admin')], 'laravel-admin-assets');
        // }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // 用 `Ioc` 容器的 `singleton` 方法和 `bind` 方法都是返回一个类的实例, 不同的是 `singleton` 是单例模式，而 `bind` 是每次返回一个新的实例
        /**
         * 绑定类
         */
        // $this->app->bind(
        //     'GeekGhc\LaraFlash\SessionStore',
        //     'GeekGhc\LaraFlash\LaravelSessionStore'
        // );

        // $this->app->singleton('yunjujigenerator',function(){
        //     return $this->app->make('Yunjuji\Generator\YunjujiGenerator');
        // });

        /**
         * 引入类
         */
        // 批量产生 `field.json` , 通过 `model.csv` 文件生成 `fields.json`
        $this->app->singleton('yunjuji.generateFieldJson', function ($app) {
            return new GenerateFieldJsonCommand();
        });
        // 批量产生脚手架, 通过遍历目录的 `fields.json` 和 `model.json`
        $this->app->singleton('yunjuji.generate', function ($app) {
            return new GenerateCommand();
        });
        // 批量回滚, 通过遍历目录的 `fields.json` 和 `model.json`
        $this->app->singleton('yunjuji.rollback', function ($app) {
            return new RollbackCommand();
        });
        // 发布命令
        $this->app->singleton('yunjuji.publish', function ($app) {
            return new PublishCommand();
        });
        // 批量填充数据
        $this->app->singleton('yunjuji.fillData', function ($app) {
            return new FillDataCommand();
        });
        // 批量删表
        $this->app->singleton('yunjuji.dropTable', function ($app) {
            return new DropTableCommand();
        });
        // 生成脚手架
        $this->app->singleton('yunjuji.scaffold', function ($app) {
            return new ScaffoldGeneratorCommand();
        });
        // 生成测试数据
        $this->app->singleton('yunjuji.generateTestData', function ($app) {
            return new GenerateTestData();
        });

        /**
         * 引入命令
         */
        $this->commands([
            'yunjuji.generateFieldJson',
            'yunjuji.generate',
            'yunjuji.rollback',
            'yunjuji.publish',
            'yunjuji.fillData',
            'yunjuji.dropTable',
            'yunjuji.scaffold',
            'yunjuji.generateTestData',
        ]);

        /**
         * laravel-admin
         */
        // $this->loadAdminAuthConfig();

        // $this->registerRouteMiddleware();

        // $this->commands($this->commands);
    }

    /**
     * Setup auth configuration.
     *
     * @return void
     */
    protected function loadAdminAuthConfig()
    {
        config(array_dot(config('admin.auth', []), 'auth.'));
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }
}
