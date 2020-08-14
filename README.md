<h1 align="center">easyswoole-permission</h1>
<p align="center">An authorization library that supports access control models like ACL, RBAC, ABAC in EasySwoole.</p>

## Installing

Require this package in the `composer.json` of your easyswoole project. This will download the package.

```shell
$ composer require
```

## Usage

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

Then you can start like this:

```php
use Easyswolle\Permission\Casbin;
use Easyswolle\Permission\Config;

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

Gets all roles:

```php
Enforcer::getAllRoles(); // ['writer', 'reader']
```

Gets all the authorization rules in the policy.:

```php
Enforcer::getPolicy();
```

Gets the roles that a user has.

```php
Enforcer::getRolesForUser('eve'); // ['writer']
```

Gets the users that has a role.

```php
Enforcer::getUsersForRole('writer'); // ['eve']
```

Determines whether a user has a role.

```php
Enforcer::hasRoleForUser('eve', 'writer'); // true or false
```

Adds a role for a user.

```php
Enforcer::addRoleForUser('eve', 'writer');
```

Adds a permission for a user or role.

```php
// to user
Enforcer::addPermissionForUser('eve', 'articles', 'read');
// to role
Enforcer::addPermissionForUser('writer', 'articles','edit');
```

Deletes a role for a user.

```php
Enforcer::deleteRoleForUser('eve', 'writer');
```

Deletes all roles for a user.

```php
Enforcer::deleteRolesForUser('eve');
```

Deletes a role.

```php
Enforcer::deleteRole('writer');
```

Deletes a permission.

```php
Enforcer::deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

Deletes a permission for a user or role.

```php
Enforcer::deletePermissionForUser('eve', 'articles', 'read');
```

Deletes permissions for a user or role.

```php
// to user
Enforcer::deletePermissionsForUser('eve');
// to role
Enforcer::deletePermissionsForUser('writer');
```

Gets permissions for a user or role.

```php
Enforcer::getPermissionsForUser('eve'); // return array
```

Determines whether a user has a permission.

```php
Enforcer::hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

See [Casbin API](https://casbin.org/docs/en/management-api) for more APIs.
