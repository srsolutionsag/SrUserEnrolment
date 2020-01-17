<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant;

use ilDatePresentation;
use ilPersonalDesktopGUI;
use ilSrUserEnrolmentPlugin;
use ilSrUserEnrolmentUIHookGUI;
use ilTemplate;
use ilUIPluginRouterGUI;
use ilUserAutoComplete;
use ilUtil;
use srag\DIC\SrUserEnrolment\DICTrait;
use srag\Plugins\SrUserEnrolment\Utils\SrUserEnrolmentTrait;

/**
 * Class AssistantsGUI
 *
 * @package           srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Assistant\AssistantsGUI: ilUIPluginRouterGUI
 */
class AssistantsGUI
{

    use DICTrait;
    use SrUserEnrolmentTrait;
    const PLUGIN_CLASS_NAME = ilSrUserEnrolmentPlugin::class;
    const CMD_BACK = "back";
    const CMD_EDIT_ASSISTANTS = "editAssistants";
    const CMD_UPDATE_ASSISTANTS = "updateAssistants";
    const CMD_USER_AUTOCOMPLETE = "userAutoComplete";
    const LANG_MODULE = "assistants";
    const TAB_EDIT_ASSISTANTS = "edit_assistants";
    /**
     * @var array
     */
    protected $assistants;


    /**
     * AssistantsGUI constructor
     */
    public function __construct()
    {

    }


    /**
     *
     */
    public function executeCommand()/*: void*/
    {
        $this->assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistantsArray(self::dic()->user()->getId());

        if (!self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess(self::dic()->user()->getId())) {
            die();
        }

        $this->setTabs();

        $next_class = self::dic()->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            default:
                $cmd = self::dic()->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_BACK:
                    case self::CMD_EDIT_ASSISTANTS:
                    case self::CMD_UPDATE_ASSISTANTS:
                    case self::CMD_USER_AUTOCOMPLETE:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }


    /**
     * @return array
     */
    public static function addAssistantsToPersonalDesktop() : array
    {
        if (self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess(self::dic()->user()->getId())) {
            $tpl = self::plugin()->template("EnrolmentWorkflow/pd_assistants.html");
            $tpl->setVariable("TITLE", self::plugin()->translate("my_assistants", self::LANG_MODULE));
            $tpl->setVariable("EDIT_LINK", self::output()->getHTML(self::dic()->ui()->factory()->link()->standard(self::plugin()->translate("edit", self::LANG_MODULE), self::dic()->ctrl()
                ->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_EDIT_ASSISTANTS))));
            $assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistants(self::dic()->user()->getId());
            if (!empty($assistants)) {
                foreach (self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getUserAssistants(self::dic()->user()->getId()) as $assistant) {
                    $tpl->setVariable("USER", $assistant->getAssistantUser()->getFullname());
                    if ($assistant->getUntil() !== null) {
                        $tpl_until = new ilTemplate(__DIR__ . "/../../../vendor/srag/custominputguis/src/PropertyFormGUI/Items/templates/input_gui_input_info.html", true, true);
                        $tpl_until->setVariable("INFO", self::plugin()->translate("until_date", self::LANG_MODULE, [
                            ilDatePresentation::formatDate($assistant->getUntil())
                        ]));
                        $tpl->setVariable("UNTIL", self::output()->getHTML($tpl_until));
                    }
                    $tpl->parseCurrentBlock();
                }
            } else {
                $tpl->setVariable("NO_ONE", self::plugin()->translate("nonone", self::LANG_MODULE));
            }

            $tpl2 = self::plugin()->template("EnrolmentWorkflow/pd_assistants.html");
            $tpl2->setVariable("TITLE", self::plugin()->translate("assistant_of", self::LANG_MODULE));
            $assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->getAssistantsOf(self::dic()->user()->getId());
            if (!empty($assistants)) {
                foreach ($assistants as $assistant) {
                    $tpl2->setVariable("USER", $assistant->getUser()->getFullname());
                    if ($assistant->getUntil() !== null) {
                        $tpl_until = new ilTemplate(__DIR__ . "/../../../vendor/srag/custominputguis/src/PropertyFormGUI/Items/templates/input_gui_input_info.html", true, true);
                        $tpl_until->setVariable("INFO", self::plugin()->translate("until_date", self::LANG_MODULE, [
                            ilDatePresentation::formatDate($assistant->getUntil())
                        ]));
                        $tpl2->setVariable("UNTIL", self::output()->getHTML($tpl_until));
                    }
                    $tpl2->parseCurrentBlock();
                }
            } else {
                $tpl2->setVariable("NO_ONE", self::plugin()->translate("nonone", self::LANG_MODULE));
            }

            return ["mode" => ilSrUserEnrolmentUIHookGUI::PREPEND, "html" => self::output()->getHTML([$tpl, $tpl2])];
        }

        return ["mode" => ilSrUserEnrolmentUIHookGUI::KEEP, "html" => ""];
    }


    /**
     *
     */
    public static function addTabs()/*: void*/
    {
        if (self::srUserEnrolment()->enrolmentWorkflow()->assistants()->hasAccess(self::dic()->user()->getId())) {
            self::dic()->tabs()->addTab(self::TAB_EDIT_ASSISTANTS, self::plugin()->translate("assistants", self::LANG_MODULE), self::dic()->ctrl()
                ->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_EDIT_ASSISTANTS));
        }
    }


    /**
     *
     */
    protected function setTabs()/*: void*/
    {
        self::dic()->tabs()->clearTargets();

        self::dic()->tabs()->setBackTarget(self::plugin()->translate("back", self::LANG_MODULE), self::dic()->ctrl()
            ->getLinkTarget($this, self::CMD_BACK));

        self::dic()->tabs()->addTab(self::TAB_EDIT_ASSISTANTS, self::plugin()->translate("assistants", self::LANG_MODULE), self::dic()->ctrl()
            ->getLinkTargetByClass(self::class, self::CMD_EDIT_ASSISTANTS));
    }


    /**
     *
     */
    protected function back()/*:void*/
    {
        self::dic()->ctrl()->redirectByClass(ilPersonalDesktopGUI::class, "jumpToProfile");
    }


    /**
     *
     */
    protected function editAssistants()/*: void*/
    {
        self::dic()->tabs()->activateTab(self::TAB_EDIT_ASSISTANTS);

        $form = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->factory()->newFormInstance($this, $this->assistants);

        self::output()->output($form, true);
    }


    /**
     *
     */
    protected function updateAssistants()/*: void*/
    {
        self::dic()->tabs()->activateTab(self::TAB_EDIT_ASSISTANTS);

        $form = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->factory()->newFormInstance($this, $this->assistants);

        if (!$form->storeForm()) {
            self::output()->output($form, true);

            return;
        }

        $this->assistants = self::srUserEnrolment()->enrolmentWorkflow()->assistants()->storeUserAssistantsArray(self::dic()->user()->getId(), $form->getAssistants());

        ilUtil::sendSuccess(self::plugin()->translate("saved", self::LANG_MODULE), true);

        self::dic()->ctrl()->redirect($this, self::CMD_EDIT_ASSISTANTS);
    }


    /**
     *
     */
    protected function userAutoComplete()/*:void*/
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(["login", "firstname", "lastname", "email", "usr_id"]);
        $auto->setMoreLinkAvailable(true);
        $auto->setResultField("usr_id");

        if (filter_input(INPUT_GET, "fetchall")) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        // TODO: Skip self

        echo $auto->getList(filter_input(INPUT_GET, "term"));

        exit;
    }
}