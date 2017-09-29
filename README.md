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
### 生成 `laravel-admin` 模板（下面给的是例子, 安装完成之后执行下列命令是ok的）
- 基本
`php artisan yunjuji:scaffold ContentTemplateType --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/content_template_type.json --datatables=true --formMode=laravel-admin --prefix=Operation`
- 如果有引用模型，则需要添加模型的 `命令空间映射` 选项(`namespaceModelMappingFile`)
`php artisan yunjuji:scaffold ContentTemplate --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/content_template.json --namespaceModelMappingFile=./vendor/yunjuji/yunjuji-generator/samples/namespace_model_mapping.json --datatables=true --formMode=laravel-admin --prefix=Operation`
- 如果需要自定义过滤区域
`php artisan yunjuji:scaffold ContentTemplate --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/content_template.json --filterFieldsFile=./vendor/yunjuji/yunjuji-generator/samples/filter.json --namespaceModelMappingFile=./vendor/yunjuji/yunjuji-generator/samples/namespace_model_mapping.json --datatables=true --formMode=laravel-admin --prefix=Operation`
### 生成 `datatables` 模板
- `php artisan yunjuji:scaffold posts --fieldsFile=./vendor/yunjuji/yunjuji-generator/samples/fields_sample.json --paginate=20 --datatables=true --prefix=v1`
# 参数说明
## 命令行说明
### yunjuji:generateFieldJson 命令, 批量生成 `field.json`
- 命令说明
	- 批量产生 `field.json` , 通过 `model.csv` 文件生成 `fields.json`: `php artisan yunjuji:generateFieldJson $PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$PATH` - 目录的绝对路径
### yunjuji:generate 命令, 批量生成脚手架
- 命令说明
	- 批量产生脚手架, 通过遍历目录的 `fields.json` 和 `model.json`: `php artisan yunjuji:generate $PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$PATH` - 目录的绝对路径
### yunjuji:rollback命令, 批量回滚
- 命令说明
	- 批量回滚, 通过遍历目录的 `fields.json` 和 `model.json`: `php artisan yunjuji:rollback $PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$PATH` - 目录的绝对路径
### infyom:rollback命令, 单个回退
- 命令说明
	- 单个脚手架回退: `php artisan infyom:rollback $MODEL_NAME $COMMAND_TYPE`
- 命令选项说明
	- `--prefix`: 命名空间前缀
- 命令参数说明
	- `$MODEL_NAME` - 模型名
	- `$COMMAND_TYPE` - Command type from api, scaffold or api_scaffold
### yunjuji:scaffold命令, 单个脚手架生成
- 命令说明
	- 生成脚手架: `php artisan yunjuji:scaffold $MODEL_NAME`
- 命令选项说明
	- `--fieldsFile`: 字段文件, 包括 `form`和 `grid`. [字段文件格式说明#](#fields-file)
	- `--filterFieldsFile`: 过滤字段文件. [过滤字段文件格式说明#](#filter-fields-file)
	- `--namespaceModelMappingFile`: 命名空间映射文件. [命名空间映射文件格式说明#](#namespace-model-mapping-file)
	- `--prefix`: 命名空间前缀
- 命令参数说明
	- `$MODEL_NAME` - 模型名
### yunjuji:publish命令, 发布
- 命令说明
	- 将自动生成的文件发布到指定目录: `php artisan yunjuji:publish $SOURCE_PATH $TARGET_PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$SOURCE_PATH` - 源目录的绝对路径
	- `$TARGET_PATH` - 目标目录的绝对路径
### yunjuji:fillData命令, 批量填充数据
- 命令说明
	- 批量填充数据: `php artisan yunjuji:fillData $PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$PATH` - 目录的绝对路径
### yunjuji:dropTable命令, 批量删表
- 命令说明
	- 批量填充数据: `php artisan yunjuji:dropTable $PATH`
- 命令选项说明
	- 无
- 命令参数说明
	- `$PATH` - 目录的绝对路径
## csv说明
### 注意点
1. 多个字段之间统一用 `;` 分隔， 最后一个字段不要加 `;`。
2. created_at, updated_at, id字段默认添加, 不需要重新指定。`
3. `fields` , `foriegns` , `inForms` 等所有属性可以不按顺序, 并且可以根据实际情况选填。比如没有关联关系的模型 `relations` 属性就没有。
4. 所有的例子以 [媒资点播类资产](http://git.oschina.net/hzyrrjjsyxgs/ott/tree/master/model/entity/asset/video/vasset)为例

### 字段详情
1. fields-字段列表
	1. **说明:**fields:【字段】【格式:字段名:字段别名;】
vasset_no: 视频编号; name: 名称; title: 标题; subtitle: 副标题; spell: 拼音; short_desc: 简述; long_desc: 详述; score: 评分; expiring_at: 失效日期; online_at: 上线日期; offline_at: 下线日期; published_at: 发布日期; season: 第几季; episode: 共几集; latest: 最近更新
2. foriegns-外键
	1. **说明:**foriegns:【外键】【格式:字段名:表名(注意一般的表名都是下划线命名法, 并且加s), 映射字段名(如果相同可以省略);】
vcat_no: vcats; vseason_no: vseasons, vseason_no; vyearcat_no: vyearcats; vareacat_no: vareacats; vareacat_no: vareacats; vlangcat_no: vlangcats
3. tags-关联关系
	1. **说明:**tags:【标签】【格式:模型名(大写,没s), 中间表名, 映射关系】
"mtm,Role,user_roles,user_id,role_id"【设计有问题】
4. relations-关联关系
	1. **说明:**relations:【关联关系】【格式: 关联关系类型(1t1:一对一, 1tm:一对多, mt1:反向一对多, m2m:多对多, hmt:远程一对多, mht:多态关联, mhm, mhtm, mhbm), 模型名(大写, 没s), 映射关系(具体分析)】
"1tm,Writer,writer_id,id"【设计有问题】
5. query【暂不处理】
6. sortby【暂不处理】
7. inForms-是否在form中显示
	1. **说明:**inForms: 【需要在编辑form里面显示的字段】【格式:字段名;】
vasset_no;name;title;subtitle;spell;short_desc;long_desc;score;expiring_at;online_at;offline_at;published_at;season;episode;latest
8. inIndexs-是否在table中显示
	1. **说明:**inIndexs:【是否需要在表格里面显示字段】【格式:字段名;】
vasset_no;name;title;subtitle;spell;short_desc;long_desc;score;expiring_at;online_at;offline_at;published_at;season;episode;latest
9. validations-校验
	1. **说明:**validations:【校验】【格式:字段名:校验规格(多个校验规则用|);】
vasset_no:required|max:100;name:required;title:required;subtitle:required;spell:required
10. htmlTypes-表单控件
	1. **说明:**htmlTypes:【控件类型】【格式:字段名:控件类型 】
vasset_no:text;name:text;title:text;subtitle:text;spell:text;short_desc:text;long_desc:textarea;score:number;expiring_at:datetime;online_at:datetime;offline_at:datetime;published_at:datetime;season:text;episode:text;latest:text
11. dbTypes-数据库字段类型
	1. **说明:**dbTypes:【数据库字段类型】【格式:字段名:字段类型,字段长度(可选, 如果没有长度则 `,` 和 字段长度都可以不要);】
vasset_no:string, 100;name:string, 100;title:string, 100;subtitle:string, 100;spell:string, 100;short_desc:string, 100; long_desc:text; score: decimal,10,2; expiring_at: timestamp;online_at: timestamp;offline_at:timestamp;published_at:timestamp;season:string, 10;episode:string, 10;latest:string, 10
12. nullables-能否为null
	1. **说明:**nullables:【能否为null】【格式:字段名;】
score;expiring_at;online_at;offline_at;published_at;season;episode;latest
13. indexs-是否为索引
	1. **说明:**indexs:【索引】【格式:字段名;(复合索引目前没考虑)】
vasset_no;name;title;subtitle;spell;short_desc;long_desc;score
## json文件说明
### 字段json文件
- 格式说明:<span id="fields-file">#</span>
	- json格式. 整个文件是一个 `[]`
- 参数说明
	- name-字段名, string
	- label-表单label, string
	- title-表格title, string
    - dbType-数据库类型, string, [支持的所有字段类型, 请参考该链接#](https://d.laravel-china.org/docs/5.5/migrations#创建字段)
    - htmlType-控件类型, string, [支持的所有控件类型, 请参考该链接#](http://laravel-admin.org/docs/#/zh/model-form-fields)
    - validations-校验, string, [支持的所有验证规则, 请参考该链接#](https://d.laravel-china.org/docs/5.5/validation#available-validation-rules)
    - relation-关联关系, string, [支持的所模型关联关系, 请参考该链接#](https://d.laravel-china.org/docs/5.5/eloquent-relationships)
    - displayField-表格里面显示关联关系的字段值, string, 格式:模型名+key+value
    - fillable-是否支持批量操作, bool
    - primary-是否为主键, bool
    - inForm-是否在表单里面显示, bool
    - inIndex-是否在表格里显示, bool
    - options-扩展的参数, string
    - searchable-可搜索的字段【目前没用】
- 例子
```
[{
    "name": "id",
    "dbType": "increments",
    "htmlType": "",
    "validations": "",
    "searchable": false,
    "fillable": false,
    "primary": true,
    "inForm": false,
    "inIndex": false
}, {
    "label": "内容模板编号",
    "title": "内容模板编号",
    "name": "template_no",
    "dbType": "string,100:nullable:index",
    "validations": "required|unique:content_templates,template_no|max:100",
    "htmlType": "text"
}, {
    "label": "内容模板名称",
    "title": "内容模板名称",
    "name": "name",
    "dbType": "string,100",
    "validations": "required|max:100",
    "htmlType": "text"
}, {
    "label": "模板别名",
    "title": "模板别名",
    "name": "alias",
    "dbType": "string,100",
    "validations": "required|max:100",
    "htmlType": "text"
}, {
    "label": "模板类型",
    "title": "模板类型",
    "name": "content_template_type_id",
    "dbType": "integer:unsigned:foreign,content_template_types,id",
    "validations": "required",
    "htmlType": "select",
    "relation": "1t1,ContentTemplateType,id,content_template_type_id",
    "displayField": "ContentTemplateType,id,alias"
}, {
    "label": "描述",
    "title": "别名",
    "name": "description",
    "dbType": "text",
    "htmlType": "textarea"
}, {
    "label": "排序",
    "title": "排序",
    "name": "listorder",
    "dbType": "integer",
    "validations": "required",
    "htmlType": "number"
}, {
    "name": "created_at",
    "dbType": "timestamp",
    "htmlType": "",
    "validations": "",
    "searchable": false,
    "fillable": false,
    "primary": false,
    "inForm": false,
    "inIndex": false
}, {
    "name": "updated_at",
    "dbType": "timestamp",
    "htmlType": "",
    "validations": "",
    "searchable": false,
    "fillable": false,
    "primary": false,
    "inForm": false,
    "inIndex": false
}]
```
### 过滤字段json文件
- 格式说明:<span id="filter-fields-file">#</span>
	- json格式. 整个文件是一个 `[]`
- 参数说明
	- label-标题
	- name-字段名
	- operator-操作符
		- equal: 等于
		- notEqual: 不等于
		- like: 模糊匹配
		- ilike: 不区分大小写
		- gt: 大于
		- lt: 小于
		- between: 范围(可以跟 `time` 或 `datetime` 等控件连用)
		- in: 某个范围内(和 `multipleSelect` 控件连用)
		- notIn: 不在某个范围(和 `multipleSelect` 控件连用)
		- date: date
		- month: month
		- year: year
		- where: 就需要自己写了(现在不支持这个)
	- htmlType-控件类型
		- select: 下拉框
		- multipleSelect: 多选(一般用来配合in和notIn两个需要查询数组的查询类型使用，也可以在where类型的查询中使用)
		- datetime: 日期
		- date: 作用和下列代码类似, $filter->equal('column')->datetime(['format' => 'YYYY-MM-DD'])
		- time: 作用和下列代码类似, $filter->equal('column')->datetime(['format' => 'HH:mm:ss'])
		- day: 作用和下列代码类似, $filter->equal('column')->datetime(['format' => 'DD'])
		- month: 作用和下列代码类似, $filter->equal('column')->datetime(['format' => 'MM'])
		- year: 作用和下列代码类似, $filter->equal('column')->datetime(['format' => 'YYYY'])
- 例子
```
[{
    "label": "内容模板类型名称",
    "name": "name",
    "operator": "like",
    "htmlType": ""
}, {
    "label": "内容模板类型别名",
    "name": "alias",
    "operator": "equal",
    "htmlType": ""
}, {
    "label": "模板类型",
    "name": "content_template_type_id",
    "operator": "in",
    "htmlType": "select"
}, {
    "label": "创建时间",
    "name": "created_at",
    "operator": "between",
    "htmlType": "datetime"
}]
```
### 命名空间映射json文件
- 格式说明<span id="namespace-model-mapping-file">#</span>
	- json格式. `key-value` 形式, `key` 表示 `模型名`, `value` 表示 `命名空间`, 整个文件是一个 `{}`
- 参数说明
	- 参数: 无. 
- 例子
```
{
    "ContentTemplateType": "App\\Models\\Operation\\ContentTemplateType"
}
```
# 本项目目前支持的能力集
1. 表(migration), 说明:相当于数据库里的表字段的定义, 作用:建表
	- 字段类型和长度, 具体支持参考[字段类型](https://d.laravel-china.org/docs/5.5/migrations#创建字段), 里面有的类型都支持
	- 支持能否为null
	- 索引
	- 外键
2. 模型(Models), 说明:表的orm
	- 1对1关联， 具体的支持参考[模型关联关系](https://d.laravel-china.org/docs/5.5/eloquent-relationships), 里面有的类型都支持
	- 1对多关联
	- 反向一对多关联
	- 多对多关联
	- 远程一对多
	- 多态关联
	- 多态多对多关联
3. 仓库(Repositories), 说明:对模型的一层封装
	- 使用 `prettus/l5-repository` 对模型进行了一层封装, 模型 `新建` 和 `修改` 操作的时候用了，目的是将一些逻辑抽离出来，避免控制器和模型冗余
4. 请求(Requests), 说明:http请求的依赖注入，做一些数据合法性校验（比如: 非空必填， 长度限制等）
	- 支持的校验规则参考[可用的验证规则#](https://d.laravel-china.org/docs/5.5/validation#available-validation-rules), 里面有的都支持
5. 路由(routes), 说明:定义uri
	- 产生crud的路由，现在会产生8项路由 例子如下:
```
列表：Route::get('entity/column/albums', ['as'=> 'entity.column.albums.index', 'uses' => 'Entity\Column\AlbumController@index']);
保存操作: Route::post('entity/column/albums', ['as'=> 'entity.column.albums.store', 'uses' => 'Entity\Column\AlbumController@store']);
新建视图: Route::get('entity/column/albums/create', ['as'=> 'entity.column.albums.create', 'uses' => 'Entity\Column\AlbumController@create']);
修改操作(全部修改): Route::put('entity/column/albums/{albums}', ['as'=> 'entity.column.albums.update', 'uses' => 'Entity\Column\AlbumController@update']);
修改操作(局部修改): Route::patch('entity/column/albums/{albums}', ['as'=> 'entity.column.albums.update', 'uses' => 'Entity\Column\AlbumController@update']);
删除操作: Route::delete('entity/column/albums/{albums}', ['as'=> 'entity.column.albums.destroy', 'uses' => 'Entity\Column\AlbumController@destroy']);
显示某条记录: Route::get('entity/column/albums/{albums}', ['as'=> 'entity.column.albums.show', 'uses' => 'Entity\Column\AlbumController@show']);
编辑视图: Route::get('entity/column/albums/{albums}/edit', ['as'=> 'entity.column.albums.edit', 'uses' => 'Entity\Column\AlbumController@edit']);
```
6. 控制器(Controllers)
	- 基本的curd功能
	- 表格(我们基本喜欢叫grid或者table)
	- 表单(form)
		- 支持的控件类型
			- 具体支持的控件类型参考[支持的控件类型](http://laravel-admin.org/docs/#/zh/model-form-fields)
	- 批量删除
	- 过滤区域
# 本项目目前还在开发的能力集
暂无