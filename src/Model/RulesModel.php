<?php

namespace EasySwoole\Permission\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * Class CasbinRulesModel
 * Create With Automatic Generator
 * @property $id
 * @property $ptype
 * @property $v0
 * @property $v1
 * @property $v2
 * @property $v3
 * @property $v4
 * @property $v5
 */
class RulesModel extends AbstractModel
{
  protected $tableName = 'casbin_rules';

  protected $autoTimeStamp = 'datetime';

  protected $primaryKey = 'id';
}
