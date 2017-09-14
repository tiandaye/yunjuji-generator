<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:32:55
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-14 10:49:30
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
        ], 'yunjuji-generator-config');
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
