<?php

declare(strict_types=1);

namespace EasySwoole\Permission\Adapters;

use Casbin\Persist\AdapterHelper;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use EasySwoole\ORM\Collection\Collection;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Permission\Model\RulesModel;
use Throwable;

class DatabaseAdapter implements Adapter
{
    use AdapterHelper;

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array $rule
     * @throws Exception
     * @throws Throwable
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . strval($key)] = $value;
        }

        RulesModel::create()->data($col, false)->save();
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     * @throws Exception
     * @throws Throwable
     */
    public function loadPolicy(Model $model): void
    {
        $instance = RulesModel::create();
        $results = $instance->all();
        if (!$results) {
            return;
        }

        if (!$results instanceof Collection) {
            $results = new Collection($results);
        }

        $rows = $results->hidden(['id', 'create_time', 'update_time'])->toArray(false, false);

        foreach ($rows as $row) {
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     * @throws Exception
     * @throws Throwable
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     * @throws Exception
     * @throws Throwable
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     * @throws Exception
     * @throws Throwable
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $instance = RulesModel::create()->where(['ptype' => $ptype]);

        foreach ($rule as $key => $value) {
            $instance->where('v' . strval($key), $value);
        }

        $instance->destroy();
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int $fieldIndex
     * @param string ...$fieldValues
     * @throws Exception
     * @throws Throwable
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $instance = RulesModel::create()->where(['ptype' => $ptype]);

        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v' . strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        $instance->destroy();
    }
}
