<h1 align="center">easyswoole-permission</h1>

<p align="center">
  	<strong>easyswoole-permission 是一个专为 EasySwoole 打造的授权工具。</strong>
</p>

<p align="center">
    <a href="https://github.com/php-casbin/easyswoole-permission/actions">
        <img src="https://github.com/php-casbin/easyswoole-permission/workflows/build/badge.svg?branch=master" alt="Build Status">
    </a>
    <a href="https://coveralls.io/github/php-casbin/easyswoole-permission">
        <img src="https://coveralls.io/repos/github/php-casbin/easyswoole-permission/badge.svg" alt="Coverage Status">
    </a>
    <a href="https://packagist.org/packages/casbin/easyswoole-permission">
        <img src="https://poser.pugx.org/casbin/easyswoole-permission/v/stable" alt="Latest Stable Version">
    </a>
     <a href="https://packagist.org/packages/casbin/easyswoole-permission">
        <img src="https://poser.pugx.org/casbin/easyswoole-permission/downloads" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/casbin/easyswoole-permissionz">
        <img src="https://poser.pugx.org/casbin/easyswoole-permission/license" alt="License">
    </a>
</p>

该扩展是基于 [Casbin](https://github.com/php-casbin/php-casbin) 开发的，一个高效的开源访问控制框架，支持基于`ACL`，`RBAC`，`ABAC`等访问控制模型。

在这之前，你需要了解 [Casbin](https://github.com/php-casbin/php-casbin) 的相关知识。

- [安装](#安装)
- [用法](#用法)
  - [数据库设置](#数据库设置)
  - [创建相应的数据表](#创建相应的数据表)
  - [快速开始](#快速开始)
  - [使用 Enfoecer Api](#使用-Enforcer-Api)
- [感谢](#感谢)
- [License](#License)

## 安装

在你的 easyswoole 应用的 `composer.json` 文件中指定该扩展。当你执行下面的 composer 命令时，该扩展会被下载。

```shell
$ composer require
```

或者，在你 easyswoole 应用的根目录（ `composer.json` 文件所在的目录）下，使用 composer 命令直接安装该扩展。

```shell
$ composer require casbin/easyswoole-permission:dev-master
```

## 用法

### 数据库设置

在 `dev.php` 文件中添加关于 mysql 的设置：
```php
/*################ MYSQL CONFIG ##################*/

'MYSQL'  => [
    'host'          => '127.0.0.1',
    'port'          => 3306,
    'user'          => 'root',
    'password'      => 'root',
    'database'      => 'easyswoole',
    'timeout'       => 5,
    'charset'       => 'utf8mb4',
]
```

然后在 `EasySwooleEvent.php` 文件中指定该设置：

```php
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

public static function initialize()
{
  ...
  $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
  DbManager::getInstance()->addConnection(new Connection($config));
}
```

### 创建相应的数据表

在使用该扩展之前，你需要创建一个叫做 `casbin_rules` 的数据表，以便 Casbin 存储相应的策略。

这里用 mysql 做示范:

```sql
CREATE TABLE  if not exists  `casbin_rules` (
  `id` BigInt(20) unsigned NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) DEFAULT NULL,
  `v0` varchar(255) DEFAULT NULL,
  `v1` varchar(255) DEFAULT NULL,
  `v2` varchar(255) DEFAULT NULL,
  `v3` varchar(255) DEFAULT NULL,
  `v4` varchar(255) DEFAULT NULL,
  `v5` varchar(255) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
```

### 快速开始

安装成功后，可以这样开始使用：

```php
use EasySwoole\Permission\Casbin;
use EasySwoole\Permission\Config;

$config = new Config();
$casbin = new Casbin($config);

// adds permissions to a user
$casbin->addPermissionForUser('eve', 'articles', 'read');
// adds a role for a user.
$casbin->addRoleForUser('eve', 'writer');
// adds permissions to a rule
$casbin->addPolicy('writer', 'articles', 'edit');
```

你可以检查一个用户是否拥有某个权限：

```php
// to check if a user has permission
if ($casbin->enforce('eve', 'articles', 'edit')) {
  // permit eve to edit articles
} else {
  // deny the request, show an error
}
```

### 使用 Enforcer Api

它提供了非常丰富的api，以促进对 Policy 的各种操作：

首先创建enforcer类的实例，后面的操作都是基于该实例进行的：

```php
$config = new Config();
$casbin = new Casbin($config);
$enforcer = $casbin->enforcer();
```

获取所有的角色：

```php
$enforcer->getAllRoles(); // ['writer', 'reader']
```

获取所有的角色的授权规则：

```php
$enforcer->getPolicy();
```

获取某个用户的所有角色：

```php
$enforcer->getRolesForUser('eve'); // ['writer']
```

获取担任某个角色的所有用户：

```php
$enforcer->getUsersForRole('writer'); // ['eve']
```

检查某个用户是否拥有某几个（某个）角色：

```php
$enforcer->hasRoleForUser('eve', 'writer'); // true or false
```

为用户添加角色：

```php
$enforcer->addRoleForUser('eve', 'writer');
```

给某个用户或角色赋予权限：

```php
// to user
$enforcer->addPermissionForUser('eve', 'articles', 'read');
// to role
$enforcer->addPermissionForUser('writer', 'articles','edit');
```

删除用户的角色：

```php
$enforcer->deleteRoleForUser('eve', 'writer');
```

删除某个用户的所有角色：

```php
$enforcer->deleteRolesForUser('eve');
```

删除单个角色：

```php
$enforcer->deleteRole('writer');
```

删除单个权限：

```php
$enforcer->deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

删除某个用户或角色的权限：

```php
$enforcer->deletePermissionForUser('eve', 'articles', 'read');
```

删除某个用户或角色的所有权限：

```php
// to user
$enforcer->deletePermissionsForUser('eve');
// to role
$enforcer->deletePermissionsForUser('writer');
```

获取指定用户或角色的所有权限：

```php
$enforcer->getPermissionsForUser('eve'); // return array
```

决定某个用户是否拥有指定的权限

```php
$enforcer->hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

参考 [Casbin API](https://casbin.org/docs/en/management-api) 来查找更多 API

## 感谢

[Casbin](https://github.com/php-casbin/php-casbin)，你可以在其 [官网](https://casbin.org/) 上查看全部文档。

## License

This project is licensed under the [Apache 2.0 license](LICENSE).

