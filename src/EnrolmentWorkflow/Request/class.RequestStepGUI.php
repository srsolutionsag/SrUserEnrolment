<?php

namespace srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Request;

use ILIAS\UI\Component\Component;
use ilLink;
use ilSession;
use ilSrUserEnrolmentPlugin;
use ilSrUserEnrolmentUIHookGUI;
use ilUIPluginRouterGUI;
use ilUtil;
use srag\DIC\SrUserEnrolment\DICTrait;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\RequiredData\FieldCtrl;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\RequiredData\FillCtrl;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Rule\AbstractRule;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Step\Step;
use srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Step\StepGUI;
use srag\Plugins\SrUserEnrolment\Utils\SrUserEnrolmentTrait;

/**
 * Class RequestStepGUI
 *
 * @package           srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Request
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Request\RequestStepGUI: ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\RequiredData\FillCtrl: srag\Plugins\SrUserEnrolment\EnrolmentWorkflow\Request\RequestStepGUI
 */
class RequestStepGUI
{

    use DICTrait;
    use SrUserEnrolmentTrait;
    const PLUGIN_CLASS_NAME = ilSrUserEnrolmentPlugin::class;
    const CMD_BACK = "back";
    const CMD_REQUEST_STEP = "requestStep";
    /**
     * @var int
     */
    protected $obj_ref_id;
    /**
     * @var Step
     */
    protected $step;


    /**
     * RequestStepGUI constructor
     */
    public function __construct()
    {

    }


    /**
     *
     */
    public function executeCommand()/*: void*/
    {
        $this->obj_ref_id = intval(filter_input(INPUT_GET, RequestsGUI::GET_PARAM_REF_ID));
        $this->step = self::srUserEnrolment()->enrolmentWorkflow()->steps()->getStepById(intval(filter_input(INPUT_GET, StepGUI::GET_PARAM_STEP_ID)));

        if (
            self::dic()->ctrl()->getCmd() !== self::CMD_BACK
            && !in_array($this->step->getStepId(),
                array_keys(self::srUserEnrolment()->enrolmentWorkflow()->steps()->getStepsForRequest(AbstractRule::TYPE_STEP_ACTION, self::dic()->user()->getId(), $this->obj_ref_id)))
        ) {
            die();
        }

        self::dic()->ctrl()->saveParameter($this, RequestsGUI::GET_PARAM_REF_ID);
        self::dic()->ctrl()->saveParameter($this, StepGUI::GET_PARAM_STEP_ID);

        $this->setTabs();

        $next_class = self::dic()->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            case strtolower(FillCtrl::class):
                self::dic()->ctrl()->forwardCommand(new FillCtrl(Step::REQUIRED_DATA_PARENT_CONTEXT_STEP, $this->step->getStepId()));
                break;

            default:
                $cmd = self::dic()->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_BACK:
                    case self::CMD_REQUEST_STEP:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }


    /**
     * @param array $a_par
     *
     * @return array
     */
    public static function addObjectActions(array $a_par) : array
    {
        $html = $a_par["html"];

        $matches = [];
        preg_match('/id="act_([0-9]+)/', $html, $matches);
        if (is_array($matches) && count($matches) >= 2) {

            $obj_ref_id = intval($matches[1]);

            self::dic()->ctrl()->setParameterByClass(self::class, RequestsGUI::GET_PARAM_REF_ID, $obj_ref_id);
            $actions = [];
            foreach (self::srUserEnrolment()->enrolmentWorkflow()->steps()->getStepsForRequest(AbstractRule::TYPE_STEP_ACTION, self::dic()->user()->getId(), $obj_ref_id) as $step) {
                self::dic()->ctrl()->setParameterByClass(self::class, StepGUI::GET_PARAM_STEP_ID, $step->getStepId());
                $actions[] = self::dic()->ui()->factory()->link()->standard('<span class="xsmall">' . $step->getActionTitle() . '</span>',
                    self::dic()->ctrl()->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_REQUEST_STEP));
            }
            if (!empty($actions)) {
                $actions_html = self::output()->getHTML(array_map(function (Component $action) : string {
                    return '<li>' . self::output()->getHTML($action) . '</li>';
                }, $actions));

                $matches = [];
                preg_match('/<ul class="dropdown-menu pull-right" role="menu" id="ilAdvSelListTable_.*">/',
                    $html, $matches);
                if (is_array($matches) && count($matches) >= 1) {
                    $html = str_ireplace($matches[0], $matches[0] . $actions_html, $html);
                } else {
                    $html = $actions_html . $html;
                }

                return ["mode" => ilSrUserEnrolmentUIHookGUI::REPLACE, "html" => $html];
            }
        }

        return ["mode" => ilSrUserEnrolmentUIHookGUI::KEEP, "html" => ""];
    }


    /**
     *
     */
    protected function setTabs()/*: void*/
    {

    }


    /**
     *
     */
    protected function back()/*: void*/
    {
        self::dic()->ctrl()->redirectToURL(ilLink::_getLink(self::dic()->tree()->getParentId($this->obj_ref_id)));
    }


    /**
     *
     */
    protected function requestStep()/*: void*/
    {
        $required_data_fields = self::srUserEnrolment()->requiredData()->fields()->getFields(Step::REQUIRED_DATA_PARENT_CONTEXT_STEP, $this->step->getStepId());

        if (!empty($required_data_fields)) {
            $required_data = self::srUserEnrolment()->requiredData()->fills()->getFillValues();

            if (empty($required_data)) {
                self::dic()->ctrl()->redirectByClass([FillCtrl::class], FillCtrl::CMD_FILL_FIELDS);

                return;
            }
        }

        self::srUserEnrolment()->enrolmentWorkflow()->requests()->request($this->obj_ref_id, $this->step->getStepId(), self::dic()->user()->getId());

        ilUtil::sendSuccess(self::plugin()->translate("requested", RequestsGUI::LANG_MODULE, [$this->step->getActionTitle()]), true);

        self::dic()->ctrl()->redirect($this, self::CMD_BACK);
    }
}