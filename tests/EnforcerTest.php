<?php

namespace EasySwoole\Permission\Tests;

use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Permission\Enforcer;
use EasySwoole\Permission\Model\RulesModel;
use PHPUnit\Framework\TestCase;

class EnforcerTest extends TestCase
{
    public function setUp():void
    {
        $conf  = [
            'host'          => MYSQL_HOST,
            'port'          => MYSQL_PORT,
            'user'          => MYSQL_USER,
            'password'      => MYSQL_PASSWORD,
            'database'      => MYSQL_DATABASE,
            'timeout'       => MYSQL_TIMEOUT,
            'charset'       => MYSQL_CHARSET,
        ];
        $config = new \EasySwoole\ORM\Db\Config($conf);
        DbManager::getInstance()->addConnection(new Connection($config));
        parent::setUp();
    }

    public function testAddPermissionsForUser()
    {
        Enforcer::addPermissionForUser('test1','route_test', 'method_test');
        $permissions = Enforcer::getPermissionsForUser('test1');
        $this->assertNotEmpty($permissions);
        $users = array_column($permissions, 0);
        $this->assertTrue(in_array('test1', $users));
        $routes = array_column($permissions, 1);
        $this->assertTrue(in_array('route_test', $routes));
        $methods = array_column($permissions, 2);
        $this->assertTrue(in_array('method_test', $methods));
    }

    public function testAddRoleForUser()
    {
        Enforcer::addRoleForUser('user_test1','role_test1');
        $roles = Enforcer::getRolesForUser('user_test1');
        $this->assertTrue(in_array('role_test1', $roles));
    }

    public function testAddRolesForUser()
    {
        Enforcer::addRolesForUser('user_test1',['role_test2','role_test3']);
        $roles = Enforcer::getRolesForUser('user_test1');
        $this->assertTrue(in_array('role_test2', $roles));
        $this->assertTrue(in_array('role_test3', $roles));
    }

    public function testHasPermissionForUser()
    {
        $this->assertTrue(Enforcer::hasPermissionForUser('test1', 'route_test','method_test'));
        $this->assertFalse(Enforcer::hasPermissionForUser('test1', 'err_route_test','err_method_test'));
    }

    public function testHasRoleForUser()
    {
        $this->assertTrue(Enforcer::hasRoleForUser('user_test1','role_test1'));
        $this->assertTrue(Enforcer::hasRoleForUser('user_test1','role_test2'));
        $this->assertTrue(Enforcer::hasRoleForUser('user_test1','role_test3'));
        $this->assertFalse(Enforcer::hasRoleForUser('user_test1','err_role_test4'));
    }

    public function testEnforce()
    {
        $this->assertTrue(Enforcer::enforce('test1','route_test','method_test'));
        $this->assertFalse(Enforcer::enforce('test1','err_route_test','err_method_test'));
    }

    public function testGetRolesForUser()
    {
        $roles = Enforcer::getRolesForUser('user_test1');
        $this->assertTrue(in_array('role_test1', $roles));
        $this->assertTrue(in_array('role_test2', $roles));
        $this->assertTrue(in_array('role_test3', $roles));
        $this->assertFalse(in_array('role_test4', $roles));
    }

    public function testGetUsersForRole()
    {
        $users = Enforcer::getUsersForRole('role_test1');
        $this->assertTrue(in_array('user_test1', $users));
        $this->assertFalse(in_array('user_test2', $users));
    }

    public function testGetPermissionsForUser()
    {
        $permissions = Enforcer::getPermissionsForUser('test1');
        $users = array_column($permissions, 0);
        $this->assertTrue(in_array('test1', $users));
        $routes = array_column($permissions, 1);
        $this->assertTrue(in_array('route_test', $routes));
        $methods = array_column($permissions, 2);
        $this->assertTrue(in_array('method_test', $methods));
    }

    public function testDeleteRoleForUser()
    {
        Enforcer::deleteRoleForUser('user_test1','role_test1');
        $this->assertFalse(Enforcer::hasRoleForUser('user_test1','role_test1'));
        $this->assertTrue(Enforcer::hasRoleForUser('user_test1','role_test2'));
    }

    public function testDeleteRolesForUser()
    {
        Enforcer::deleteRolesForUser('user_test1');
        $this->assertFalse(Enforcer::hasRoleForUser('user_test1','role_test2'));
        $this->assertFalse(Enforcer::hasRoleForUser('user_test1','role_test3'));
    }

    public function testDeleteUser()
    {
        Enforcer::addRoleForUser('user_test1','role_test1');
        Enforcer::addRoleForUser('user_test2','role_test1');
        Enforcer::deleteUser('user_test1');
        $users = Enforcer::getUsersForRole('role_test1');
        $this->assertTrue(in_array('user_test2', $users));
        $this->assertFalse(in_array('user_test1', $users));
    }

    public function testDeleteRole()
    {
        $this->assertTrue(Enforcer::hasRoleForUser('user_test2','role_test1'));
        Enforcer::addRoleForUser('user_test2','role_test2');
        Enforcer::deleteRole('role_test1');
        $this->assertTrue(Enforcer::hasRoleForUser('user_test2','role_test2'));
        $this->assertFalse(Enforcer::hasRoleForUser('user_test2','role_test1'));
    }

    public function testDeletePermission()
    {
        $this->assertTrue(Enforcer::enforce('test1','route_test','method_test'));
        Enforcer::addPermissionForUser('role_test2','role_route_test2','role_method_test2');
        $this->assertTrue(Enforcer::enforce('user_test2','role_route_test2','role_method_test2'));
        Enforcer::deletePermission('route_test','method_test');
        $this->assertFalse(Enforcer::enforce('test1','route_test','method_test'));
        $this->assertTrue(Enforcer::enforce('user_test2','role_route_test2','role_method_test2'));
    }

    public function testDeletePermissionForUser()
    {
        Enforcer::deletePermissionForUser('role_test2','role_route_test2','role_method_test2');
        $this->assertFalse(Enforcer::enforce('user_test2','role_route_test2','role_method_test2'));
    }

    public function testDeletePermissionsForUser()
    {
        Enforcer::addPermissionForUser('role_test2','role_route_test1','role_method_test1');
        Enforcer::addPermissionForUser('role_test2','role_route_test2','role_method_test2');
        $this->assertTrue(Enforcer::enforce('user_test2','role_route_test1','role_method_test1'));
        $this->assertTrue(Enforcer::enforce('user_test2','role_route_test2','role_method_test2'));
        Enforcer::deletePermissionsForUser('role_test2');
        $this->assertFalse(Enforcer::enforce('user_test2','role_route_test1','role_method_test1'));
        $this->assertFalse(Enforcer::enforce('user_test2','role_route_test2','role_method_test2'));
        $this->assertFalse(Enforcer::enforce('role_test2','role_route_test1','role_method_test1'));
        $this->assertFalse(Enforcer::enforce('role_test2','role_route_test2','role_method_test2'));
        Enforcer::deleteUser('user_test2');
    }
}