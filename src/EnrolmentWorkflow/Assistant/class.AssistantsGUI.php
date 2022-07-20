<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant;

require_once __DIR__ . "/../../../vendor/autoload.php";

use ilAdministrationGUI;
use ilDashboardGUI;
use ilDatePresentation;
use ilObjUserGUI;
use ilPersonalDesktopGUI;
use ilSrUserEnrolmentPlugin;
use ilUIPluginRouterGUI;
use ilUtil;
use srag\CustomInputGUIs\SrUserEnrolment\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl;
use srag\CustomInputGUIs\SrUserEnrolment\Template\Template;
use srag\DIC\SrUserEnrolment\DICTrait;
use srag\Plugins\SrUserEnrolment\Utils\SrUserEnrolmentTrait;

/**
 * Class AssistantsGUI
 *
 * @package           srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant
 *
 * @ilCtrl_isCalledBy srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant\AssistantsGUI: ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant\AssistantsGUI: srag\CustomInputGUIs\SrUserEnrolment\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl
 */
class AssistantsGUI
{

    use DICTrait;
    use SrUserEnrolmentTrait;

    const CMD_BACK = "back";
    const CMD_EDIT_ASSISTANTS = "editAssistants";
    const CMD_UPDATE_ASSISTANTS = "updateAssistants";
    const GET_PARAM_USER_ID = "user_id";
    const LANG_MODULE = "assistants";
    const PLUGIN_CLASS_NAME = ilSrUserEnrolmentPlugin::class;
    const TAB_EDIT_ASSISTANTS = "edit_assistants";
    /**
     * @var array
     */
    protected $assistants;
    /**
     * @var int
     */
    protected $user_id;


    /**
     * AssistantsGUI constructor
     */
    public function __construct()
    {

    }


    /**
     * @param int $user_id
     */
    public static function addTabs(int $user_id) : void
    {
        if (self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess($user_id)) {
            self::dic()->ctrl()->setParameterByClass(self::class, self::GET_PARAM_USER_ID, $user_id);
            self::dic()
                ->tabs()
                ->addTab(self::TAB_EDIT_ASSISTANTS, self::plugin()->translate(($user_id === intval(self::dic()->user()->getId()) ? "my_" : "") . "assistants", self::LANG_MODULE), self::dic()->ctrl()
                    ->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_EDIT_ASSISTANTS));
        }
    }


    /**
     * @param int $user_id
     *
     * @return string
     */
    public static function getAssistantsForPersonalDesktop(int $user_id) : string
    {
        if (!self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess($user_id)) {
            return "";
        }

        self::dic()->ctrl()->setParameterByClass(self::class, self::GET_PARAM_USER_ID, $user_id);

        $tpl = self::plugin()->template("EnrolmentWorkflow/pd_assistants.html");
        $tpl->setVariableEscaped("TITLE", self::plugin()->translate("my_assistants", self::LANG_MODULE));
        $tpl->setVariable("EDIT_LINK", self::output()->getHTML(self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("edit", self::LANG_MODULE), self::dic()->ctrl()
            ->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_EDIT_ASSISTANTS))));
        $assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistants($user_id);
        if (!empty($assistants)) {
            $tpl->setCurrentBlock("assistants");

            foreach (self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistants($user_id) as $assistant) {
                $tpl->setVariableEscaped("USER", $assistant->getAssistantUser()->getFullname());
                if ($assistant->getUntil() !== null) {
                    $tpl_until = new Template(__DIR__ . "/../../../vendor/srag/custominputguis/src/PropertyFormGUI/Items/templates/input_gui_input_info.html", true, true);
                    $tpl_until->setVariableEscaped("INFO", self::plugin()->translate("until_date", self::LANG_MODULE, [
                        ilDatePresentation::formatDate($assistant->getUntil())
                    ]));
                    $tpl->setVariable("UNTIL", self::output()->getHTML($tpl_until));
                }
                $tpl->parseCurrentBlock();
            }
        } else {
            $tpl->setVariableEscaped("NO_ONE", self::plugin()->translate("nonone", self::LANG_MODULE));
        }

        $tpl2 = self::plugin()->template("EnrolmentWorkflow/pd_assistants.html");
        $tpl2->setVariableEscaped("TITLE", self::plugin()->translate("assistant_of", self::LANG_MODULE));
        $assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getAssistantsOf($user_id);
        if (!empty($assistants)) {
            $tpl2->setCurrentBlock("assistants");

            foreach ($assistants as $assistant) {
                $tpl2->setVariableEscaped("USER", $assistant->getUser()->getFullname());
                if ($assistant->getUntil() !== null) {
                    $tpl_until = new Template(__DIR__ . "/../../../vendor/srag/custominputguis/src/PropertyFormGUI/Items/templates/input_gui_input_info.html", true, true);
                    $tpl_until->setVariableEscaped("INFO", self::plugin()->translate("until_date", self::LANG_MODULE, [
                        ilDatePresentation::formatDate($assistant->getUntil())
                    ]));
                    $tpl2->setVariable("UNTIL", self::output()->getHTML($tpl_until));
                }
                $tpl2->parseCurrentBlock();
            }
        } else {
            $tpl2->setVariableEscaped("NO_ONE", self::plugin()->translate("nonone", self::LANG_MODULE));
        }

        return self::output()->getHTML([$tpl, $tpl2]);
    }


    /**
     *
     */
    public function executeCommand() : void
    {
        $this->user_id = intval(filter_input(INPUT_GET, self::GET_PARAM_USER_ID));

        if (!self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess($this->user_id)) {
            die();
        }

        self::dic()->ctrl()->saveParameter($this, self::GET_PARAM_USER_ID);

        $this->assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistantsArray($this->user_id);

        $this->setTabs();

        $next_class = self::dic()->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            case strtolower(UsersAjaxAutoCompleteCtrl::class):
                self::dic()->ctrl()->forwardCommand(new UsersAjaxAutoCompleteCtrl());
                break;

            default:
                $cmd = self::dic()->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_BACK:
                    case self::CMD_EDIT_ASSISTANTS:
                    case self::CMD_UPDATE_ASSISTANTS:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }


    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }


    /**
     *
     */
    protected function back() : void
    {
        if ($this->user_id === intval(self::dic()->user()->getId())) {
            self::dic()->ctrl()->redirectByClass(ilDashboardGUI::class, "jumpToProfile");
        } else {
            self::dic()->ctrl()->setParameterByClass(ilObjUserGUI::class, "ref_id", 7);
            self::dic()->ctrl()->setParameterByClass(ilObjUserGUI::class, "admin_mode", "settings");
            self::dic()->ctrl()->setParameterByClass(ilObjUserGUI::class, "obj_id", $this->user_id);
            self::dic()->ctrl()->redirectByClass([ilAdministrationGUI::class, ilObjUserGUI::class], "view");
        }
    }


    /**
     *
     */
    protected function editAssistants() : void
    {
        self::dic()->tabs()->activateTab(self::TAB_EDIT_ASSISTANTS);

        $form = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->factory()->newFormInstance($this, $this->assistants);

        self::output()->output($form, true);
    }


    /**
     *
     */
    protected function setTabs() : void
    {
        self::dic()->tabs()->clearTargets();

        self::dic()->tabs()->setBackTarget(self::plugin()->translate("back", self::LANG_MODULE), self::dic()->ctrl()
            ->getLinkTarget($this, self::CMD_BACK));

        self::dic()
            ->tabs()
            ->addTab(self::TAB_EDIT_ASSISTANTS, self::plugin()->translate(($this->user_id === intval(self::dic()->user()->getId()) ? "my_" : "") . "assistants", self::LANG_MODULE), self::dic()->ctrl()
                ->getLinkTarget($this, self::CMD_EDIT_ASSISTANTS));
    }


    /**
     *
     */
    protected function updateAssistants() : void
    {
        self::dic()->tabs()->activateTab(self::TAB_EDIT_ASSISTANTS);

        $form = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->factory()->newFormInstance($this, $this->assistants);

        if (!$form->storeForm()) {
            self::output()->output($form, true);

            return;
        }

        $this->assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->storeUserAssistantsArray($this->user_id, $form->getAssistants());

        ilUtil::sendSuccess(self::plugin()->translate("saved", self::LANG_MODULE), true);

        self::dic()->ctrl()->redirect($this, self::CMD_EDIT_ASSISTANTS);
    }
}
