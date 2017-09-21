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
然后运行下面的命令完成安装：
```
php artisan admin:install
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
`php artisan yunjuji:scaffold posts --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/fields_sample.json --paginate=20 --datatables=true --formMode=laravel-admin --prefix=v1`
