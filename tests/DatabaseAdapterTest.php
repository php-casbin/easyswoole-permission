<?php

namespace EasySwoole\Permission\Tests;

use PHPUnit\Framework\TestCase;
use EasySwoole\ORM\DbManager;
use EasySwoole\Permission\Model\RulesModel;
use EasySwoole\Permission\Casbin;
use EasySwoole\Permission\Config;
use EasySwoole\ORM\Db\Connection;
use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\Persist\Adapters\Filter;

class DatabaseAdapterTest extends TestCase
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

    protected function initDb()
    {
        RulesModel::create()->destroy(null, true);
        RulesModel::create(['ptype' => 'p', 'v0'  => 'alice', 'v1' => 'data1', 'v2' => 'read'])->save();
        RulesModel::create(['ptype' => 'p', 'v0'  => 'bob', 'v1' => 'data2', 'v2' => 'write'])->save();
        RulesModel::create(['ptype' => 'p', 'v0'  => 'data2_admin', 'v1' => 'data2', 'v2' => 'read'])->save();
        RulesModel::create(['ptype' => 'p', 'v0'  => 'data2_admin', 'v1' => 'data2', 'v2' => 'write'])->save();
        RulesModel::create(['ptype' => 'g', 'v0'  => 'alice', 'v1' => 'data2_admin'])->save();
    }

    protected function getEnforcer()
    {
        $config = new Config();
        $casbin = new Casbin($config);
        $this->initDb();
        return $casbin->enforcer();
    }

    public function testRemovePolicy()
    {
        $e = $this->getEnforcer();
        $this->assertFalse($e->enforce('alice', 'data5', 'read'));
        $e->addPermissionForUser('alice', 'data5', 'read');
        $this->assertTrue($e->enforce('alice', 'data5', 'read'));
        $e->deletePermissionForUser('alice', 'data5', 'read');
        $this->assertFalse($e->enforce('alice', 'data5', 'read'));
    }

    public function testLoadPolicy()
    {
        $e = $this->getEnforcer();
        $this->assertTrue($e->enforce('alice', 'data1', 'read'));
        $this->assertFalse($e->enforce('bob', 'data1', 'read'));
        $this->assertTrue($e->enforce('bob', 'data2', 'write'));
        $this->assertTrue($e->enforce('alice', 'data2', 'read'));
        $this->assertTrue($e->enforce('alice', 'data2', 'write'));
    }

    public function testAddPolicy()
    {
        $e = $this->getEnforcer();
        $this->assertFalse($e->enforce('eve', 'data3', 'read'));
        $e->addPermissionForUser('eve', 'data3', 'read');
        $this->assertTrue($e->enforce('eve', 'data3', 'read'));
    }

    public function testRemoveFilteredPolicy()
    {
        $e = $this->getEnforcer();
        $this->assertTrue($e->enforce('alice', 'data1', 'read'));
        $e->removeFilteredPolicy(1, 'data1');
        $this->assertFalse($e->enforce('alice', 'data1', 'read'));
        $this->assertTrue($e->enforce('bob', 'data2', 'write'));
        $this->assertTrue($e->enforce('alice', 'data2', 'read'));
        $this->assertTrue($e->enforce('alice', 'data2', 'write'));
        $e->removeFilteredPolicy(1, 'data2', 'read');
        $this->assertTrue($e->enforce('bob', 'data2', 'write'));
        $this->assertFalse($e->enforce('alice', 'data2', 'read'));
        $this->assertTrue($e->enforce('alice', 'data2', 'write'));
        $e->removeFilteredPolicy(2, 'write');
        $this->assertFalse($e->enforce('bob', 'data2', 'write'));
        $this->assertFalse($e->enforce('alice', 'data2', 'write'));
    }

    public function testSavePolicy()
    {
        $e = $this->getEnforcer();
        $this->assertFalse($e->enforce('alice', 'data4', 'read'));

        $model = $e->getModel();
        $model->clearPolicy();
        $model->addPolicy('p', 'p', ['alice', 'data4', 'read']);

        $adapter = $e->getAdapter();
        $adapter->savePolicy($model);
        $this->assertTrue($e->enforce('alice', 'data4', 'read'));
    }

    public function testAddPolicies()
    {
        $policies = [
            ['u1', 'd1', 'read'],
            ['u2', 'd2', 'read'],
            ['u3', 'd3', 'read'],
        ];
        $e = $this->getEnforcer();
        $e->clearPolicy();
        $this->assertEquals([], $e->getPolicy());
        $e->addPolicies($policies);
        $this->assertEquals($policies, $e->getPolicy());
    }

    public function testRemovePolicies()
    {
        $e = $this->getEnforcer();
        $this->assertEquals([
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write'],
            ['data2_admin', 'data2', 'read'],
            ['data2_admin', 'data2', 'write'],
        ], $e->getPolicy());

        $e->removePolicies([
            ['data2_admin', 'data2', 'read'],
            ['data2_admin', 'data2', 'write'],
        ]);

        $this->assertEquals([
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write']
        ], $e->getPolicy());
    }

    public function testLoadFilteredPolicy()
    {
        $e = $this->getEnforcer();
        $e->clearPolicy();
        $adapter = $e->getAdapter();
        $adapter->setFiltered(true);
        $this->assertEquals([], $e->getPolicy());

        // invalid filter type
        try {
            $filter = ['alice', 'data1', 'read'];
            $e->loadFilteredPolicy($filter);
            $exception = InvalidFilterTypeException::class;
            $this->fail("Expected exception $exception not thrown");
        } catch (InvalidFilterTypeException $exception) {
            $this->assertEquals("invalid filter type", $exception->getMessage());
        }

        // string
        $filter = "v0 = bob";
        $e->loadFilteredPolicy($filter);
        $this->assertEquals([
            ['bob', 'data2', 'write']
        ], $e->getPolicy());

        // Filter
        $filter = new Filter(['v2'], ['read']);
        $e->loadFilteredPolicy($filter);
        $this->assertEquals([
            ['alice', 'data1', 'read'],
            ['data2_admin', 'data2', 'read'],
        ], $e->getPolicy());

        // Closure
        $e->loadFilteredPolicy(function ($query) {
            $query->where('v1', 'data1');
        });

        $this->assertEquals([
            ['alice', 'data1', 'read'],
        ], $e->getPolicy());
    }

    public function testUpdatePolicy()
    {
        $e = $this->getEnforcer();

        $this->assertEquals([
            ['alice', 'data1', 'read'],
            ['bob', 'data2', 'write'],
            ['data2_admin', 'data2', 'read'],
            ['data2_admin', 'data2', 'write'],
        ], $e->getPolicy());

        $e->updatePolicy(
            ['alice', 'data1', 'read'],
            ['alice', 'data1', 'write']
        );

        $e->updatePolicy(
            ['bob', 'data2', 'write'],
            ['bob', 'data2', 'read']
        );

        $this->assertEquals([
            ['alice', 'data1', 'write'],
            ['bob', 'data2', 'read'],
            ['data2_admin', 'data2', 'read'],
            ['data2_admin', 'data2', 'write'],
        ], $e->getPolicy());
    }
}