Yunjuji Generator
***
# 安装
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
- 下面正式安装 `yunjuji/yunjuji-generator` 开发阶段使用下面命令
```
composer require yunjuji/yunjuji-generator:dev-dev --prefer-source
```
# 使用
## 生成脚手架(cms), 使用下面命令
`php artisan yunjuji:scaffold posts --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/fields_sample.json --paginate=20 --datatables=true --formMode=laravel-admin --prefix=v1`
