Yunjuji Generator
***
# 安装
## 安装自动产生的依赖
- 在 `composer.json` 中引入
```
"infyomlabs/laravel-generator": "5.5.x-dev",
"laravelcollective/html": "^5.5.0",
"infyomlabs/adminlte-templates": "5.5.x-dev",
"infyomlabs/swagger-generator": "dev-master",
"jlapp/swaggervel": "dev-master",
"doctrine/dbal": "~2.3"
```
- 执行 `composer update`
- 在 `config/app.php` 的 `providers` 加入
```
Collective\Html\HtmlServiceProvider::class,
Laracasts\Flash\FlashServiceProvider::class,
Prettus\Repository\Providers\RepositoryServiceProvider::class,
\InfyOm\Generator\InfyOmGeneratorServiceProvider::class,
\InfyOm\AdminLTETemplates\AdminLTETemplatesServiceProvider::class,
```
- 在 `config/app.php` 的 `aliases` 加入
```
'Form'      => Collective\Html\FormFacade::class,
'Html'      => Collective\Html\HtmlFacade::class,
'Flash'     => Laracasts\Flash\Flash::class,
```
- 依次执行 
```
php artisan vendor:publish
php artisan infyom:publish
php artisan infyom.publish:layout
```
## 引入自定义路由
- 在 `app/routes` 目录下面新建 `web` 文件夹 
- 在 `app/Providers/RouteServiceProvider.php` 的 `map()` 函数中加入
```
// tian add `mapCustomRoutes`
$this->mapCustomRoutes();
```
- 在 `app/Providers/RouteServiceProvider.php` 加入下面两个函数
```
    /**
     * tian add
     * Define the "Custom" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapCustomRoutes()
    {
        /**
         * 加载 routes/web 文件夹下的路由
         */
        Route::group([
            'middleware' => ['web', 'admin', 'admin.bootstrap', 'admin.pjax', 'admin.log', 'admin.bootstrap', 'admin.permission'], // `laravel-admin` 有的中间件 'admin.auth', 'admin.pjax', 'admin.log', 'admin.bootstrap', 'admin.permission'
            'namespace'  => 'App\Http\Controllers',
            'prefix'     => 'admin',
        ], function ($router) {
            // Route::group(['middleware' => ['auth:' . config('inventory.base.guard'), 'menu', 'authAdmin']], function () {
            $routePath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web';
            $this->getFilePath($routePath);
            // });
        });
    }

    /**
     * [getFilePath 递归遍历文件]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    protected function getFilePath($path = '.')
    {
        // opendir()返回一个目录句柄,失败返回false
        $current_dir = opendir($path);
        // readdir()返回打开目录句柄中的一个条目
        while (($file = readdir($current_dir)) !== false) {
            // 构建子目录路径
            $sub_dir = $path . DIRECTORY_SEPARATOR . $file;
            if ($file == '.' || $file == '..') {
                continue;
                // 如果是目录,进行递归
            } else if (is_dir($sub_dir)) {
                // echo $sub_dir . '<br />';
                $this->getFilePath($sub_dir);
            } else {
                // 如果是文件,直接输出
                $path = substr($path, strrpos($path, 'routes'));
                // echo base_path($path . DIRECTORY_SEPARATOR . $file) . '<br />';
                require base_path($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
```
## 安装 `laravel-admin`
首先确保安装好了 `laravel`，并且数据库连接设置正确。
```
composer require encore/laravel-admin "1.5.*"
```
然后运行下面的命令来发布资源：
```
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
```
在该命令会生成配置文件 `config/admin.php`，可以在里面修改安装的地址、数据库连接、以及表名，建议都是用默认配置不修改。
生成如下文件:
```
Copied Directory [\vendor\encore\laravel-admin\config] To [\config]
Copied Directory [\vendor\encore\laravel-admin\resources\lang] To [\resources\la
ng]
Copied Directory [\vendor\encore\laravel-admin\database\migrations] To [\databas
e\migrations]
Copied Directory [\vendor\encore\laravel-admin\resources\assets] To [\public\ven
dor\laravel-admin]
Publishing complete.
```
建议将 `config/admin.php` 里面的 `database` 部分的表名添加 `laravel_` 前缀. 一共九张表.
然后运行下面的命令完成安装：
```
php artisan admin:install
```
生成的文件：
```
Migrating: 2016_01_04_173148_create_admin_tables
Migrated:  2016_01_04_173148_create_admin_tables
Admin directory was created: \app\Admin
HomeController file was created: \app\Admin/Controllers/HomeController.php
ExampleController file was created: \app\Admin/Controllers/ExampleController.php
Bootstrap file was created: \app\Admin/bootstrap.php
Routes file was created: \app\Admin/routes.php
```
## 正式安装 `yunjuji/yunjuji-generator`
- 开发阶段使用下面命令
```
composer require yunjuji/yunjuji-generator:dev-dev --prefer-source
```
- 卸载
`composer remove yunjuji/yunjuji-generator`
# 使用
## 生成脚手架(cms), 使用下面命令
- 生成 `laravel-admin` 模板, 使用
`php artisan infyom:scaffold ContentTemplateType --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/content_template_type.json --datatables=true --formMode=laravel-admin --prefix=Operation`
- 生成 `datatables` 模板, 使用
`php artisan yunjuji:scaffold posts --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/fields_sample.json --paginate=20 --datatables=true --prefix=v1`
