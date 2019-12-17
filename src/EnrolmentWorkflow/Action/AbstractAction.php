<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Action;

use ActiveRecord;
use arConnector;
use ilSrUserEnrolmentPlugin;
use LogicException;
use srag\DIC\SrUserEnrolment\DICTrait;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\AbstractRule;
use srag\Plugins\SrUserEnrolment\Utils\SrUserEnrolmentTrait;

/**
 * Class AbstractAction
 *
 * @package srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractAction extends ActiveRecord
{

    use DICTrait;
    use SrUserEnrolmentTrait;
    /**
     * @var string
     *
     * @abstract
     */
    const TABLE_NAME_SUFFIX = "";
    const PLUGIN_CLASS_NAME = ilSrUserEnrolmentPlugin::class;


    /**
     * @inheritDoc
     */
    public static function getTableName() : string
    {
        if (empty(static::TABLE_NAME_SUFFIX)) {
            throw new LogicException("table name suffix is empty!");
        }

        return ilSrUserEnrolmentPlugin::PLUGIN_ID . "_act_" . static::TABLE_NAME_SUFFIX;
    }


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return static::getTableName();
    }


    /**
     * @return string
     *
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return static::getTableName();
    }


    /**
     * @return string
     */
    public static function getType() : string
    {
        return strtolower(end(explode("\\", static::class)));
    }


    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     * @con_sequence     true
     */
    protected $action_id;
    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected $step_id;
    /**
     * @var bool
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected $enabled = true;
    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected $sort;


    /**
     * AbstractAction constructor
     *
     * @param int              $primary_key_value
     * @param arConnector|null $connector
     */
    public function __construct(/*int*/ $primary_key_value = 0, arConnector $connector = null)
    {
        parent::__construct($primary_key_value, $connector);
    }


    /**
     * @return string
     */
    public function getTypeTitle() : string
    {
        return self::plugin()->translate("type_" . static::getType(), ActionsGUI::LANG_MODULE);
    }


    /**
     * @return string
     */
    public function getActionTitle() : string
    {
        return $this->getTypeTitle();
    }


    /**
     * @return string
     */
    public abstract function getActionDescription() : string;


    /**
     * @return string
     */
    public function getIfDescription() : string
    {
        $descriptions = array_map(function (AbstractRule $rule) : string {
            return $rule->getRuleTitle();
        }, self::srUserEnrolment()->enrolmentWorkflow()
            ->rules()
            ->getRules(AbstractRule::PARENT_CONTEXT_ACTION, AbstractRule::TYPE_ACTION_IF, $this->getId()));

        return nl2br(implode("\n", $descriptions), false);
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return self::getType() . "_" . $this->action_id;
    }


    /**
     * @param string $field_name
     *
     * @return mixed|null
     */
    public function sleep(/*string*/ $field_name)
    {
        $field_value = $this->{$field_name};

        switch ($field_name) {
            case "enabled":
                return ($field_value ? 1 : 0);

            default:
                return null;
        }
    }


    /**
     * @param string $field_name
     * @param mixed  $field_value
     *
     * @return mixed|null
     */
    public function wakeUp(/*string*/ $field_name, $field_value)
    {
        switch ($field_name) {
            case "enabled":
                return boolval($field_value);

            default:
                return null;
        }
    }


    /**
     * @return int
     */
    public function getActionId() : int
    {
        return $this->action_id;
    }


    /**
     * @param int $action_id
     */
    public function setActionId(int $action_id)/*: void*/
    {
        $this->action_id = $action_id;
    }


    /**
     * @return int
     */
    public function getStepId() : int
    {
        return $this->step_id;
    }


    /**
     * @param int $step_id
     */
    public function setStepId(int $step_id)/*: void*/
    {
        $this->step_id = $step_id;
    }


    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }


    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)/*: void*/
    {
        $this->enabled = $enabled;
    }


    /**
     * @return int
     */
    public function getSort() : int
    {
        return $this->sort;
    }


    /**
     * @param int $sort
     */
    public function setSort(int $sort)/*: void*/
    {
        $this->sort = $sort;
    }
}