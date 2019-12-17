<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\Fields\Operator;

use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\RulesGUI;
const OPERATOR_EQUALS = 1;
const OPERATOR_STARTS_WITH = 2;
const OPERATOR_CONTAINS = 3;
const OPERATOR_ENDS_WITH = 4;
const OPERATOR_REG_EX = 5;
const OPERATOR_EQUALS_SUBSEQUENT = 6;
const OPERATOR_LESS = 7;
const OPERATOR_LESS_EQUALS = 8;
const OPERATOR_BIGGER = 9;
const OPERATOR_BIGGER_EQUALS = 10;
const OPERATORS
= [
    OPERATOR_EQUALS        => "equals",
    OPERATOR_STARTS_WITH   => "starts_with",
    OPERATOR_CONTAINS      => "contains",
    OPERATOR_ENDS_WITH     => "ends_with",
    OPERATOR_REG_EX        => "reg_ex",
    OPERATOR_LESS          => "less",
    OPERATOR_LESS_EQUALS   => "less_equals",
    OPERATOR_BIGGER        => "bigger",
    OPERATOR_BIGGER_EQUALS => "bigger_equals"
];
const OPERATORS_SUBSEQUENT
= [
    OPERATOR_EQUALS            => "equals",
    OPERATOR_EQUALS_SUBSEQUENT => "equals_subsequent"
];

/**
 * Trait Operator
 *
 * @package srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\Fields\Operator
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
trait Operator
{

    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       2
     * @con_is_notnull   true
     */
    protected $operator = OPERATOR_EQUALS;
    /**
     * @var bool
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected $operator_negated = false;
    /**
     * @var bool
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected $operator_case_sensitive = false;


    /**
     * @return string
     */
    protected function getOperatorTitle() : string
    {
        return self::plugin()->translate("operator_" . (OPERATORS[$this->operator] ?? OPERATORS_SUBSEQUENT[$this->operator]), RulesGUI::LANG_MODULE);
    }


    /**
     * @param string $field_name
     * @param mixed  $field_value
     *
     * @return mixed
     */
    protected function sleepOperator(string $field_name, $field_value)
    {
        switch ($field_name) {
            case "operator_case_sensitive":
            case "operator_negated":
                return ($field_value ? 1 : 0);

            default:
                return null;
        }
    }


    /**
     * @param string $field_name
     * @param mixed  $field_value
     *
     * @return mixed
     */
    protected function wakeUpOperator(string $field_name, $field_value)
    {
        switch ($field_name) {
            case "operator_case_sensitive":
            case "operator_negated":
                return boolval($field_value);

            default:
                return null;
        }
    }


    /**
     * @return int
     */
    public function getOperator() : int
    {
        return $this->operator;
    }


    /**
     * @param int $operator
     */
    public function setOperator(int $operator)/* : void*/
    {
        $this->operator = $operator;
    }


    /**
     * @return bool
     */
    public function isOperatorNegated() : bool
    {
        return $this->operator_negated;
    }


    /**
     * @param bool $operator_negated
     */
    public function setOperatorNegated(bool $operator_negated)/* : void*/
    {
        $this->operator_negated = $operator_negated;
    }


    /**
     * @return bool
     */
    public function isOperatorCaseSensitive() : bool
    {
        return $this->operator_case_sensitive;
    }


    /**
     * @param bool $operator_case_sensitive
     */
    public function setOperatorCaseSensitive(bool $operator_case_sensitive)/* : void*/
    {
        $this->operator_case_sensitive = $operator_case_sensitive;
    }
}