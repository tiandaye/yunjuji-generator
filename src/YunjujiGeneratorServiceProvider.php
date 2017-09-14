<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:32:55
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-14 10:35:51
 */

namespace Yunjuji\Generator;

use Illuminate\Support\ServiceProvider;
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
        $configPath = __DIR__.'/../config/generator.php';

        $this->publishes([
            $configPath => config_path('yunjuji/generator.php'),
        ]);
        // $this->publishes([__DIR__.'/path/to/assets' => public_path('vendor/courier'),], 'public');php artisan vendor:publish --tag=public --force,
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
		// 	'GeekGhc\LaraFlash\SessionStore',
		// 	'GeekGhc\LaraFlash\LaravelSessionStore'
		// );

		// $this->app->singleton('yunjujigenerator',function(){
		// 	return $this->app->make('Yunjuji\Generator\YunjujiGenerator');
		// });

    	/**
         * 引入类
         */
        $this->app->singleton('yunjuji.scaffold', function ($app) {
            return new ScaffoldGeneratorCommand();
        });

        /**
         * 引入命令
         */
        $this->commands([
            'yunjuji.scaffold',
        ]);

		/**
		 * infyom
		 */
 //        $this->app->singleton('infyom.publish', function ($app) {
 //            return new GeneratorPublishCommand();
 //        });

 //        $this->app->singleton('infyom.api', function ($app) {
 //            return new APIGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.scaffold', function ($app) {
 //            return new ScaffoldGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.publish.layout', function ($app) {
 //            return new LayoutPublishCommand();
 //        });

 //        $this->app->singleton('infyom.publish.templates', function ($app) {
 //            return new PublishTemplateCommand();
 //        });

 //        $this->app->singleton('infyom.api_scaffold', function ($app) {
 //            return new APIScaffoldGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.migration', function ($app) {
 //            return new MigrationGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.model', function ($app) {
 //            return new ModelGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.repository', function ($app) {
 //            return new RepositoryGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.api.controller', function ($app) {
 //            return new APIControllerGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.api.requests', function ($app) {
 //            return new APIRequestsGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.api.tests', function ($app) {
 //            return new TestsGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.scaffold.controller', function ($app) {
 //            return new ControllerGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.scaffold.requests', function ($app) {
 //            return new RequestsGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.scaffold.views', function ($app) {
 //            return new ViewsGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.rollback', function ($app) {
 //            return new RollbackGeneratorCommand();
 //        });

 //        $this->app->singleton('infyom.vuejs', function ($app) {
 //            return new VueJsGeneratorCommand();
 //        });
 //        $this->app->singleton('infyom.publish.vuejs', function ($app) {
 //            return new VueJsLayoutPublishCommand();
 //        });

 //        $this->commands([
 //            'infyom.publish',
 //            'infyom.api',
 //            'infyom.scaffold',
 //            'infyom.api_scaffold',
 //            'infyom.publish.layout',
 //            'infyom.publish.templates',
 //            'infyom.migration',
 //            'infyom.model',
 //            'infyom.repository',
 //            'infyom.api.controller',
 //            'infyom.api.requests',
 //            'infyom.api.tests',
 //            'infyom.scaffold.controller',
 //            'infyom.scaffold.requests',
 //            'infyom.scaffold.views',
 //            'infyom.rollback',
 //            'infyom.vuejs',
 //            'infyom.publish.vuejs',
 //        ]);


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
