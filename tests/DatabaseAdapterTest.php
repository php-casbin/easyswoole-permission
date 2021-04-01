<?php

namespace EasySwoole\Permission\Tests;

use PHPUnit\Framework\TestCase;
use EasySwoole\ORM\DbManager;
use EasySwoole\Permission\Model\RulesModel;
use EasySwoole\Permission\Casbin;
use EasySwoole\Permission\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\EasySwoole\Config as ESConfig;

class DatabaseAdapterTest extends TestCase
{
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
        $this->initConfig();
        $config = new Config();
        $casbin = new Casbin($config);
        $this->initDb();
        return $casbin->enforcer();
    }

    protected function initConfig()
    {
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        $conf = $instance->getConf();
        $conf['MYSQL'] = [
            'host'          => '127.0.0.1',
            'port'          => 3306,
            'user'          => 'root',
            'password'      => '',
            'database'      => 'easyswoole_permission',
            'timeout'       => 5,
            'charset'       => 'utf8mb4',
        ];
        $instance->load($conf);
        $config = new \EasySwoole\ORM\Db\Config(ESConfig::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new Connection($config));
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
}