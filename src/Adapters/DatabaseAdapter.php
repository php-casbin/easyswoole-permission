<?php

namespace EasySwoole\Permission\Adapters;

use Casbin\Persist\AdapterHelper;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use EasySwoole\ORM\Collection\Collection;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Permission\Model\RulesModel;
use Throwable;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\Persist\UpdatableAdapter;
use EasySwoole\ORM\DbManager;

class DatabaseAdapter implements Adapter, BatchAdapter, FilteredAdapter, UpdatableAdapter
{
    use AdapterHelper;

    /**
     * @var bool
     */
    private $filtered = false;

    /**
     * Filter the rule.
     *
     * @param array $rule
     *
     * @return array
     */
    public function filterRule(array $rule): array
    {
        $rule = array_values($rule);

        $i = count($rule) - 1;
        for (; $i >= 0; $i--) {
            if ($rule[$i] != '' && !is_null($rule[$i])) {
                break;
            }
        }

        return array_slice($rule, 0, $i + 1);
    }

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
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     * @return array
     * @throws Exception
     * @throws Throwable
     */
    public function _removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, ?string ...$fieldValues): array
    {
        $removedRules = [];
        $instance = RulesModel::create()->where(['ptype' => $ptype]);

        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v' . strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        $results = (clone $instance)->all();
        if (!$results instanceof Collection) {
            $results = new Collection($results);
        }

        $oldP = $results->hidden(['id', 'ptype', 'create_time', 'update_time'])->toArray(false, false);
        foreach ($oldP as &$item) {
            $item = $this->filterRule($item);
            $removedRules[] = $item;
        }

        $instance->destroy(null, true);

        return $removedRules;
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
    }

    /**
     * adds a policy rules to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rules
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $cols = [];

        foreach($rules as $rule) {
            $temp = [];
            $temp['ptype'] = $ptype;
            foreach ($rule as $key => $value) {
                $temp['v'.strval($key)] = $value;
            }
            $cols[] = $temp;
        }
        RulesModel::create()->saveAll($cols);
    }

    /**
     * removes policy rules from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rules
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        $ids = [];

        foreach($rules as $rule) {
            $where = [];
            $where['ptype'] = $ptype;
            foreach ($rule as $key => $value) {
                $where['v'.strval($key)] = $value;
            }
            $ret = RulesModel::create()->get($where);
            $ret && $ids[] = $ret->id;
        }

        RulesModel::create()->destroy($ids);
    }

    /**
     * loads only policy rules that match the filter.
     *
     * @param Model $model
     * @param mixed $filter
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $instance = RulesModel::create();

        if (is_string($filter)) {
            $filter = str_replace(' ', '', $filter);
            $filter = explode('=', $filter);
            $instance->where($filter[0], $filter[1]);
        } else if ($filter instanceof Filter) {
            foreach($filter->p as $k => $v) {
                $where[$v] = $filter->g[$k];
                $instance->where($v, $filter->g[$k]);
            }
        } else if ($filter instanceof \Closure) {
            $instance->where($filter);
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        $rows = $instance->all();

        foreach ($rows as $row) {
            $row = $row->hidden(['create_time','update_time', 'id'])->toArray();
            $row = array_filter($row, function($value) { return !is_null($value) && $value !== ''; });
            //var_dump($row);
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
        $this->setFiltered(true);
    }

    /**
     * Returns true if the loaded policy has been filtered.
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * Sets filtered parameter.
     *
     * @param bool $filtered
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * Updates a policy rule from storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[] $oldRule
     * @param string[] $newPolicy
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $instance = RulesModel::create();
        $where = [];
        $update = [];
        $where['ptype'] = $ptype;

        foreach ($oldRule as $key => $value) {
            $where['v' . $key] = $value;
        }

        $instance = $instance->where($where)->get();
        foreach ($newPolicy as $key => $value) {
            $update['v' . $key] = $value;
        }

        $instance->update($update);
    }

    /**
     * UpdatePolicies updates some policy rules to storage, like db, redis.
     *
     * @param string     $sec
     * @param string     $ptype
     * @param string[][] $oldRules
     * @param string[][] $newRules
     *
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        try {
            // start transaction
            DbManager::getInstance()->startTransaction();

            foreach ($oldRules as $i => $oldRule) {
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }

            // commit transaction
            DbManager::getInstance()->commit();
        } catch (\Throwable $e) {
            // rollback transaction
            DbManager::getInstance()->rollback();
        }
    }

    /**
     * UpdateFilteredPolicies deletes old rules and adds new rules.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $newPolicies
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     *
     * @return array
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        $oldRules = [];
        try {
            // start transaction
            DbManager::getInstance()->startTransaction();

            $oldRules = $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
            $this->addPolicies($sec, $ptype, $newPolicies);

            // commit transaction
            DbManager::getInstance()->commit();
        } catch (\Throwable $e) {
            // rollback transaction
            DbManager::getInstance()->rollback();
        }

        return $oldRules;
    }
}
