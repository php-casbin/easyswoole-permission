<?php

namespace EasySwoole\Permission;

use Casbin\Exceptions\CasbinException;

class EnforcerIDE
{
    /**
     * @var \Casbin\Enforcer
     */
    protected static $instance;

    /**
     * @param string $name
     * @param string ...$domain
     * @return array
     */
    public static function getRolesForUser(string $name, string ...$domain): array
    {
        return self::$instance->getRolesForUser($name, ...$domain);
    }

    /**
     * @param string $name
     * @param string ...$domain
     * @return array
     */
    public static function getUsersForRole(string $name, string ...$domain): array
    {
        return self::$instance->getUsersForRole($name, ...$domain);
    }

    /**
     * @param string $name
     * @param string $role
     * @param string ...$domain
     * @return bool
     */
    public static function hasRoleForUser(string $name, string $role, string ...$domain): bool
    {
        return self::$instance->hasRoleForUser($name, $role, ...$domain);
    }

    /**
     * @param string $user
     * @param string $role
     * @param string ...$domain
     * @return bool
     */
    public static function addRoleForUser(string $user, string $role, string ...$domain): bool
    {
        return self::$instance->addRoleForUser($user, $role, ...$domain);
    }

    /**
     * @param string $user
     * @param array $roles
     * @param string ...$domain
     * @return bool
     */
    public static function addRolesForUser(string $user, array $roles, string ...$domain): bool
    {
        return self::$instance->addRolesForUser($user, $roles, ...$domain);
    }

    /**
     * @param string $user
     * @param string $role
     * @param string ...$domain
     * @return bool
     */
    public static function deleteRoleForUser(string $user, string $role, string ...$domain): bool
    {
        return self::$instance->deleteRoleForUser($user, $role, ...$domain);
    }

    /**
     * @param string $user
     * @param string ...$domain
     * @return bool
     * @throws CasbinException
     */
    public static function deleteRolesForUser(string $user, string ...$domain): bool
    {
        return self::$instance->deleteRolesForUser($user, ...$domain);
    }

    /**
     * @param string $user
     * @return bool
     */
    public static function deleteUser(string $user): bool
    {
        return self::$instance->deleteUser($user);
    }

    /**
     * @param string $role
     * @return bool
     */
    public static function deleteRole(string $role): bool
    {
        return self::$instance->deleteRole($role);
    }

    /**
     * @param string ...$permission
     * @return bool
     */
    public static function deletePermission(string ...$permission): bool
    {
        return self::$instance->deletePermission(...$permission);
    }

    /**
     * @param string $user
     * @param string ...$permission
     * @return bool
     */
    public static function addPermissionForUser(string $user, string ...$permission): bool
    {
        return self::$instance->addPermissionForUser($user, ...$permission);
    }

    /**
     * @param string $user
     * @param string ...$permission
     * @return bool
     */
    public static function deletePermissionForUser(string $user, string ...$permission): bool
    {
        return self::$instance->deletePermissionForUser($user, ...$permission);
    }

    /**
     * @param string $user
     * @return bool
     */
    public static function deletePermissionsForUser(string $user): bool
    {
        return self::$instance->deletePermissionsForUser($user);
    }

    /**
     * @param string $user
     * @return array
     */
    public static function getPermissionsForUser(string $user): array
    {
        return self::$instance->getPermissionsForUser($user);
    }

    /**
     * @param string $user
     * @param string ...$permission
     * @return bool
     */
    public static function hasPermissionForUser(string $user, string ...$permission): bool
    {
        return self::$instance->hasPermissionForUser($user, ...$permission);
    }

    /**
     * @param string $name
     * @param string ...$domain
     * @return array
     */
    public static function getImplicitRolesForUser(string $name, string ...$domain): array
    {
        return self::$instance->getImplicitRolesForUser($name, ...$domain);
    }

    /**
     * @param string $user
     * @param string ...$domain
     * @return array
     * @throws CasbinException
     */
    public static function getImplicitPermissionsForUser(string $user, string ...$domain): array
    {
        return self::$instance->getImplicitPermissionsForUser($user, ...$domain);
    }

    /**
     * @param string ...$permission
     * @return array
     * @throws CasbinException
     */
    public static function getImplicitUsersForPermission(string ...$permission): array
    {
        return self::$instance->getImplicitUsersForPermission(...$permission);
    }

    /**
     * @param string $name
     * @param string $domain
     * @return array
     */
    public static function getUsersForRoleInDomain(string $name, string $domain): array
    {
        return self::$instance->getUsersForRoleInDomain($name, ...$domain);
    }

    /**
     * @param string $name
     * @param string $domain
     * @return array
     */
    public static function getRolesForUserInDomain(string $name, string $domain): array
    {
        return self::$instance->getRolesForUserInDomain($name, ...$domain);
    }

    /**
     * @param string $name
     * @param string $domain
     * @return array
     */
    public static function getPermissionsForUserInDomain(string $name, string $domain): array
    {
        return self::$instance->getPermissionsForUserInDomain($name, ...$domain);
    }

    /**
     * @param string $user
     * @param string $role
     * @param string $domain
     * @return bool
     */
    public static function addRoleForUserInDomain(string $user, string $role, string $domain): bool
    {
        return self::$instance->addRoleForUserInDomain($user, $role, $domain);
    }

    /**
     * @param string $user
     * @param string $role
     * @param string $domain
     * @return bool
     */
    public static function deleteRoleForUserInDomain(string $user, string $role, string $domain): bool
    {
        return self::$instance->addRoleForUserInDomain($user, $role, $domain);
    }

    /**
     * @param mixed ...$rvals
     * @return bool
     * @throws CasbinException
     */
    public static function enforce(...$rvals): bool
    {
        return self::$instance->enforce(...$rvals);
    }
}