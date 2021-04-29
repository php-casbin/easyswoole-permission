<?php

namespace EasySwoole\Permission;

use Casbin\Enforcer;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use EasySwoole\Permission\Adapters\DatabaseAdapter;
use Casbin\Log\Log;

/**
 * Class Casbin
 * @package EasySwoole\Permission
 * @method array getRolesForUser(string $name)
 * @method array getUsersForRole(string $name)
 * @method bool hasRoleForUser(string $name, string $role)
 * @method bool addRoleForUser(string $user, string $role)
 * @method bool deleteRoleForUser(string $user, string $role)
 * @method bool deleteRolesForUser(string $user)
 * @method bool deleteUser($user)
 * @method bool deleteRole(string $role)
 * @method bool deletePermission(string ...$permission)
 * @method bool addPermissionForUser(string $user, string ...$permission)
 * @method bool deletePermissionForUser(string $user, string ...$permission)
 * @method bool deletePermissionsForUser(string $user)
 * @method array getPermissionsForUser(string $user)
 * @method bool hasPermissionForUser(string $user, string ...$permission)
 * @method array getImplicitRolesForUser(string $name, string ...$domain)
 * @method array getImplicitPermissionsForUser(string $user, string ...$domain)
 * @method array getImplicitUsersForPermission(string ...$permission)
 * @method array getUsersForRoleInDomain(string $name, string $domain)
 * @method array getRolesForUserInDomain(string $name, string $domain)
 * @method array getPermissionsForUserInDomain(string $name, string $domain)
 * @method bool addRoleForUserInDomain(string $user, string $role, string $domain)
 * @method bool deleteRoleForUserInDomain(string $user, string $role, string $domain)
 * @method bool addPolicyInternal(string $sec, string $ptype, array $rule)
 * @method bool addPoliciesInternal(string $sec, string $ptype, array $rules)
 * @method bool removePolicyInternal(string $sec, string $ptype, array $rule)
 * @method bool removePoliciesInternal(string $sec, string $ptype, array $rules)
 * @method bool removeFilteredPolicyInternal(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method void checkWatcher()
 * @method bool ShouldPersist()
 * @method array getAllSubjects()
 * @method array getAllNamedSubjects(string $ptype)
 * @method array getAllObjects()
 * @method array getAllNamedObjects(string $ptype)
 * @method array getAllActions()
 * @method array getAllNamedActions(string $ptype)
 * @method array getAllRoles()
 * @method array getAllNamedRoles(string $ptype)
 * @method array getPolicy()
 * @method array getFilteredPolicy(int $fieldIndex, string ...$fieldValues)
 * @method array getNamedPolicy(string $ptype)
 * @method array getFilteredNamedPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method array getGroupingPolicy()
 * @method array getFilteredGroupingPolicy(int $fieldIndex, string ...$fieldValues)
 * @method array getNamedGroupingPolicy(string $ptype)
 * @method array getFilteredNamedGroupingPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method bool hasPolicy(...$params)
 * @method bool hasNamedPolicy(string $ptype, ...$params)
 * @method bool addPolicy(...$params)
 * @method bool addPolicies(array $rules)
 * @method bool addNamedPolicy(string $ptype, ...$params)
 * @method bool addNamedPolicies(string $ptype, array $rules)
 * @method bool removePolicy(...$params)
 * @method bool removePolicies(array $rules)
 * @method bool removeFilteredPolicy(int $fieldIndex, string ...$fieldValues)
 * @method bool removeNamedPolicy(string $ptype, ...$params)
 * @method bool removeNamedPolicies(string $ptype, array $rules)
 * @method bool removeFilteredNamedPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method bool hasGroupingPolicy(...$params)
 * @method bool hasNamedGroupingPolicy(string $ptype, ...$params)
 * @method bool addGroupingPolicy(...$params)
 * @method bool addGroupingPolicies(array $rules)
 * @method bool addNamedGroupingPolicy(string $ptype, ...$params)
 * @method bool addNamedGroupingPolicies(string $ptype, array $rules)
 * @method bool removeGroupingPolicy(...$params)
 * @method bool removeGroupingPolicies(array $rules)
 * @method bool removeFilteredGroupingPolicy(int $fieldIndex, string ...$fieldValues)
 * @method bool removeNamedGroupingPolicy(string $ptype, ...$params)
 * @method bool removeNamedGroupingPolicies(string $ptype, array $rules)
 * @method bool removeFilteredNamedGroupingPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method void addFunction(string $name, \Closure $func)
 * @method bool enforce(...$rvals)
 */
class Casbin
{
    /**
     * @var Enforcer
     */
    protected $enforcer;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(?Config $config = null)
    {

        if (!$config instanceof Config) {
            $config = new Config();
        }

        $this->config = $config;

        $this->adapter = $config->getAdapter() ?? new DatabaseAdapter();

        if ($loggerClass = $this->config->getLoggerClass()) {
            $refClass = new \ReflectionClass($loggerClass);
            if ($refClass->isSubclassOf(\Casbin\Log\Logger::class)) {
                /** @var \Casbin\Log\Logger $logger */
                $logger = $refClass->newInstance();
                Log::setLogger($logger);
            }
        }

        $this->model = new Model();

        if (Config::CONFIG_TYPE_FILE === $config->getModelConfigType()) {
            $this->model->loadModel($config->getModelConfigFilePath());
        } elseif (Config::CONFIG_TYPE_TEXT === $config->getModelConfigType()) {
            $this->model->loadModelFromText($config->getModelConfigText());
        }
    }

    public function enforcer($newInstance = false)
    {
        if ($newInstance || is_null($this->enforcer)) {
            $this->enforcer = new Enforcer($this->model, $this->adapter, $this->config->isLogEnable());
        }

        return $this->enforcer;
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->enforcer(), $name], $params);
    }
}
