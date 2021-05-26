<?php

namespace srag\RequiredData\SrUserEnrolment\Field\Field\Integer;

use ILIAS\UI\Component\Input\Field\Input;
use ilNumberInputGUI;
use srag\CustomInputGUIs\SrUserEnrolment\InputGUIWrapperUIInputComponent\InputGUIWrapperUIInputComponent;
use srag\RequiredData\SrUserEnrolment\Fill\AbstractFillField;

/**
 * Class IntegerFillField
 *
 * @package srag\RequiredData\SrUserEnrolment\Field\Field\Integer
 */
class IntegerFillField extends AbstractFillField
{

    /**
     * @var IntegerField
     */
    protected $field;


    /**
     * @inheritDoc
     */
    public function __construct(IntegerField $field)
    {
        parent::__construct($field);
    }


    /**
     * @inheritDoc
     */
    public function formatAsJson($fill_value)
    {
        return intval($fill_value);
    }


    /**
     * @inheritDoc
     */
    public function formatAsString($fill_value) : string
    {
        return strval($fill_value);
    }


    /**
     * @inheritDoc
     */
    public function getInput() : Input
    {
        // self::dic()->ui()->factory()->input()->field()->numeric has no min and max value?!
        $input = (new InputGUIWrapperUIInputComponent(new ilNumberInputGUI($this->field->getLabel())))->withByline($this->field->getDescription())->withRequired($this->field->isRequired());

        if ($this->field->getMinValue() !== null) {
            $input->getInput()->setMinValue($this->field->getMinValue());
        }

        if ($this->field->getMaxValue() !== null) {
            $input->getInput()->setMaxValue($this->field->getMaxValue());
        }

        return $input;
    }
}
