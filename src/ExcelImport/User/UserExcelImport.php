<?php

namespace srag\Plugins\SrUserEnrolment\ExcelImport\User;

use srag\Plugins\SrUserEnrolment\ExcelImport\ExcelImport;
use srag\Plugins\SrUserEnrolment\ExcelImport\ExcelImportFormGUI;
use stdClass;

/**
 * Class UserExcelImport
 *
 * @package srag\Plugins\SrUserEnrolment\ExcelImport\User
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class UserExcelImport extends ExcelImport
{

    /**
     * @inheritDoc
     */
    protected function getUpdateFields(array $fields) : array
    {
        $update_fields = parent::getUpdateFields($fields);

        switch ($this->parent::getObjType($this->parent->getObjRefId(), $this->parent->getObjSingleId())) {
            case "role":
            case "usrf":
                break;

            case "cat":
            case "orgu":
            default:
                $update_fields[self::FIELDS_TYPE_ILIAS]["time_limit_owner"] = true;
                break;
        }

        return $update_fields;
    }


    /**
     * @inheritDoc
     */
    protected function handleRoles(ExcelImportFormGUI $form, stdClass &$user)/*: void*/
    {
        parent::handleRoles($form, $user);

        switch ($this->parent::getObjType($this->parent->getObjRefId(), $this->parent->getObjSingleId())) {
            case "role":
                $user->{ExcelImportFormGUI::KEY_FIELDS}->{self::FIELDS_TYPE_ILIAS}->roles[] = $this->parent->getObjSingleId();
                $user->{ExcelImportFormGUI::KEY_FIELDS}->{self::FIELDS_TYPE_ILIAS}->roles = array_unique($user->{ExcelImportFormGUI::KEY_FIELDS}->{self::FIELDS_TYPE_ILIAS}->roles);
                break;

            case "cat":
            case "orgu":
            case "usrf":
            default:
                break;
        }
    }


    /**
     * @inheritDoc
     */
    protected function handleLocalUserAdministration(ExcelImportFormGUI $form, stdClass &$user)/*: void*/
    {
        switch ($this->parent::getObjType($this->parent->getObjRefId(), $this->parent->getObjSingleId())) {
            case "role":
            case "usrf":
                break;

            case "cat":
            case "orgu":
            default:
                $user->{ExcelImportFormGUI::KEY_FIELDS}->{self::FIELDS_TYPE_ILIAS}->time_limit_owner = $this->parent->getObjRefId();
                break;
        }
    }


    /**
     * @inheritDoc
     */
    public function getUsersToEnroll() : array
    {
        self::dic()->ctrl()->redirectByClass(UserExcelImportGUI::class, UserExcelImportGUI::CMD_BACK);

        return [];
    }
}