<?php

namespace srag\RequiredData\SrUserEnrolment\Field\Field\Radio\Form;

use srag\RequiredData\SrUserEnrolment\Field\Field\Radio\RadioField;
use srag\RequiredData\SrUserEnrolment\Field\Field\Select\Form\SelectFieldFormBuilder;
use srag\RequiredData\SrUserEnrolment\Field\FieldCtrl;

/**
 * Class RadioFieldFormBuilder
 *
 * @package srag\RequiredData\SrUserEnrolment\Field\Field\Radio\Form
 */
class RadioFieldFormBuilder extends SelectFieldFormBuilder
{

    /**
     * @var RadioField
     */
    protected $field;


    /**
     * @inheritDoc
     */
    public function __construct(FieldCtrl $parent, RadioField $field)
    {
        parent::__construct($parent, $field);
    }
}
