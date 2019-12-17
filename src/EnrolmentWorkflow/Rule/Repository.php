<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule;

use ilSrUserEnrolmentPlugin;
use srag\DIC\SrUserEnrolment\DICTrait;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\Group\Group;
use srag\Plugins\SrUserEnrolment\Utils\SrUserEnrolmentTrait;

/**
 * Class Repository
 *
 * @package srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class Repository
{

    use DICTrait;
    use SrUserEnrolmentTrait;
    const PLUGIN_CLASS_NAME = ilSrUserEnrolmentPlugin::class;
    /**
     * @var self
     */
    protected static $instance = null;


    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Repository constructor
     */
    private function __construct()
    {

    }


    /**
     * @param AbstractRule[] $rules
     *
     * @return Group|null
     */
    public function createGroupOfRules(array $rules)/* : ?Group*/
    {
        $rules = array_filter($rules, function (AbstractRule $rule) : bool {
            return !($rule instanceof Group);
        });
        if (empty($rules)) {
            return null;
        }

        $first_rule = current($rules);

        $rules = array_filter($rules, function (AbstractRule $rule) use ($first_rule): bool {
            return ($rule->getType() === $first_rule->getType() && $rule->getParentContext() === $first_rule->getParentContext() && $rule->getParentId() === $first_rule->getParentId());
        });
        if (empty($rules)) {
            return null;
        }

        /**
         * @var Group $group
         */
        $group = $this->factory()->newInstance(Group::getRuleType());

        $group->setType($first_rule->getType());
        $group->setParentContext($first_rule->getParentContext());
        $group->setParentId($first_rule->getParentId());
        $this->storeRule($group);

        foreach ($rules as $rule) {
            $rule->setType(AbstractRule::TYPE_RULE_GROUP);
            $rule->setParentContext(AbstractRule::PARENT_CONTEXT_RULE_GROUP);
            $rule->setParentId($group->getRuleId());
            $this->storeRule($rule);

            if ($rule->isEnabled()) {
                $group->setEnabled(true);
            }
        }

        $this->storeRule($group);

        return $group;
    }


    /**
     * @param AbstractRule $rule
     */
    public function deleteRule(AbstractRule $rule)/*: void*/
    {
        $rule->delete();

        if ($rule instanceof Group) {
            $this->deleteRules(AbstractRule::PARENT_CONTEXT_RULE_GROUP, $rule->getRuleId());
        }
    }


    /**
     * @param int    $parent_context
     * @param string $parent_id
     */
    public function deleteRules(int $parent_context, string $parent_id)/*: void*/
    {
        foreach (AbstractRule::TYPES[$parent_context] as $type => $type_lang_key) {
            foreach ($this->getRules($parent_context, $type, $parent_id, false) as $rule) {
                $this->deleteRule($rule);
            }
        }
    }


    /**
     * @internal
     */
    public function dropTables()/*:void*/
    {
        foreach ($this->factory()->getRuleTypes() as $class) {
            self::dic()->database()->dropTable($class::getTableName(), false);
        }
    }


    /**
     * @return Factory
     */
    public function factory() : Factory
    {
        return Factory::getInstance();
    }


    /**
     * @param int    $parent_context
     * @param string $parent_id
     * @param int    $type
     * @param int    $user_id
     * @param int    $obj_ref_id
     * @param bool   $and_operator
     *
     * @return AbstractRule[]
     */
    public function getCheckedRules(int $parent_context, string $parent_id, int $type, int $user_id, int $obj_ref_id, bool $and_operator = false) : array
    {
        $rules = $this->getRules($parent_context, $type, $parent_id);
        if (empty($rules)) {
            return [];
        }

        $checked_rules = array_filter($rules, function (AbstractRule $rule) use ($user_id, $obj_ref_id): bool {
            return $this->factory()->newCheckerInstance($rule)->check($user_id, $obj_ref_id);
        });
        if (empty($checked_rules)) {
            return [];
        }

        if ($and_operator) {
            if (count($rules) === count($checked_rules)) {
                return $rules;
            } else {
                return [];
            }
        } else {
            return $checked_rules;
        }
    }


    /**
     * @param int    $parent_context
     * @param string $parent_id
     * @param string $rule_type
     * @param int    $rule_id
     *
     * @return AbstractRule|null
     */
    public function getRuleById(int $parent_context, string $parent_id, string $rule_type, int $rule_id)/*: ?AbstractRule*/
    {
        foreach ($this->factory()->getRuleTypes() as $rule_type_class => $class) {
            if ($rule_type_class === $rule_type) {
                /**
                 * @var AbstractRule|null $rule
                 */
                $rule = $class::where(["parent_context" => $parent_context, "parent_id" => $parent_id, "rule_id" => $rule_id])->first();

                return $rule;
            }
        }

        return null;
    }


    /**
     * @param int         $parent_context
     * @param int         $type
     * @param string|null $parent_id
     * @param bool        $only_enabled
     *
     * @return AbstractRule[]
     */
    public function getRules(int $parent_context, int $type,  /*?*/ string $parent_id = null, bool $only_enabled = true) : array
    {
        $rules = [];

        foreach ($this->factory()->getRuleTypes($parent_context) as $class) {
            $where = $class::where(["parent_context" => $parent_context, "type" => $type]);

            if (!empty($parent_id)) {
                $where = $where->where(["parent_id" => $parent_id]);
            }

            if ($only_enabled) {
                $where = $where->where(["enabled" => true]);
            }

            /**
             * @var AbstractRule $rule
             */
            foreach ($where->get() as $rule) {
                $rules[$rule->getId()] = $rule;
            }
        }

        return $rules;
    }


    /**
     * @internal
     */
    public function installTables()/*:void*/
    {
        foreach ($this->factory()->getRuleTypes() as $class) {
            $class::updateDB();
        }
    }


    /**
     * @param AbstractRule $rule
     */
    public function storeRule(AbstractRule $rule)/*: void*/
    {
        $rule->store();
    }


    /**
     * @param Group $group
     *
     * @return AbstractRule[]
     */
    public function ungroup(Group $group) : array
    {
        $rules = $this->getRules(AbstractRule::PARENT_CONTEXT_RULE_GROUP, AbstractRule::TYPE_RULE_GROUP, $group->getRuleId(), false);

        foreach ($rules as $rule) {
            $rule->setType($group->getType());
            $rule->setParentContext($group->getParentContext());
            $rule->setParentId($group->getParentId());
            $this->storeRule($rule);
        }

        $this->deleteRule($group);

        return $rules;
    }
}