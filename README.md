<h1 align="center">easyswoole-permission</h1>

<p align="center">
    <strong>easyswoole-permission is an authorization library for the easyswoole framework.</strong>    
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

[Chinese Version](https://github.com/php-casbin/easyswoole-permission/blob/master/README_CN.md)

It's based on [Casbin](https://github.com/php-casbin/php-casbin), an authorization library that supports access control models like `ACL`, `RBAC`, `ABAC`.

All you need to learn to use `Casbin` first.

* [Installation](#installation)

* [Usage](#usage)

  - [Database settings](#database-settings)
  - [Create corresponding data table](#create-corresponding-data-table)

  * [Quick start](#quick-start)
  * [Using Enforcer Api](#using-enforcer-api)

* [Thinks](#thinks)

* [License](#license)

## Installation

Require this package in the `composer.json` of your easyswoole project. This will download the package.

```shell
$ composer require
```

Or in the root directory of your easyswoole application, you can use the following composer command to install this package directly .

```shell
$ composer require casbin/easyswoole-permission:dev-master
```

## Usage

### Database settings

add mysql configuration to `dev.php`:
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

add mysql configuration to `EasySwooleEvent.php`:

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

### Create corresponding data table

Before using it, you need to create a table named `casbin_rules` for Casbin to store the policy.

Take mysql as an example:

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

### Quick start

Then you can start like this:

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

You can check if a user has a permission like this:

```php
// to check if a user has permission
if ($casbin->enforce('eve', 'articles', 'edit')) {
  // permit eve to edit articles
} else {
  // deny the request, show an error
}
```

### Using Enforcer Api

It provides a very rich api to facilitate various operations on the Policy:

First create an instance of the enforcer class, and the following operations are based on this instance:

```php
$config = new Config();
$casbin = new Casbin($config);
$enforcer = $casbin->enforcer();
```

Gets all roles:

```php
$enforcer->getAllRoles(); // ['writer', 'reader']
```

Gets all the authorization rules in the policy.:

```php
$enforcer->getPolicy();
```

Gets the roles that a user has.

```php
$enforcer->getRolesForUser('eve'); // ['writer']
```

Gets the users that has a role.

```php
$enforcer->getUsersForRole('writer'); // ['eve']
```

Determines whether a user has a role.

```php
$enforcer->hasRoleForUser('eve', 'writer'); // true or false
```

Adds a role for a user.

```php
$enforcer->addRoleForUser('eve', 'writer');
```

Adds a permission for a user or role.

```php
// to user
$enforcer->addPermissionForUser('eve', 'articles', 'read');
// to role
$enforcer->addPermissionForUser('writer', 'articles','edit');
```

Deletes a role for a user.

```php
$enforcer->deleteRoleForUser('eve', 'writer');
```

Deletes all roles for a user.

```php
$enforcer->deleteRolesForUser('eve');
```

Deletes a role.

```php
$enforcer->deleteRole('writer');
```

Deletes a permission.

```php
$enforcer->deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

Deletes a permission for a user or role.

```php
$enforcer->deletePermissionForUser('eve', 'articles', 'read');
```

Deletes permissions for a user or role.

```php
// to user
$enforcer->deletePermissionsForUser('eve');
// to role
$enforcer->deletePermissionsForUser('writer');
```

Gets permissions for a user or role.

```php
$enforcer->getPermissionsForUser('eve'); // return array
```

Determines whether a user has a permission.

```php
$enforcer->hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

See [Casbin API](https://casbin.org/docs/en/management-api) for more APIs.

## Thinks

[Casbin](https://github.com/php-casbin/php-casbin) in Easyswoole. You can find the full documentation of Casbin [on the website](https://casbin.org/).

## License

This project is licensed under the [Apache 2.0 license](LICENSE).
