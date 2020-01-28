<?php

namespace srag\RequiredData\SrUserEnrolment\Field\Text;

use srag\RequiredData\SrUserEnrolment\Field\AbstractFieldFormGUI;
use srag\RequiredData\SrUserEnrolment\Field\FieldCtrl;

/**
 * Class TextFieldFormGUI
 *
 * @package srag\RequiredData\SrUserEnrolment\Field\Text
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TextFieldFormGUI extends AbstractFieldFormGUI
{

    /**
     * @var TextField
     */
    protected $object;


    /**
     * @inheritDoc
     */
    public function __construct(FieldCtrl $parent, TextField $object)
    {
        parent::__construct($parent, $object);
    }
}
