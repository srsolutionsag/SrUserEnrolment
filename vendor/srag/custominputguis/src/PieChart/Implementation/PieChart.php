<?php

namespace srag\CustomInputGUIs\SrUserEnrolment\PieChart\Implementation;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use InvalidArgumentException;
use srag\CustomInputGUIs\SrUserEnrolment\PieChart\Component\PieChart as PieChartInterface;
use srag\CustomInputGUIs\SrUserEnrolment\PieChart\Component\PieChartItem as PieChartItemInterface;

/**
 * Class PieChart
 *
 * https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/src/UI/Implementation/Component/Chart/PieChart/PieChart.php
 *
 * @package srag\CustomInputGUIs\SrUserEnrolment\PieChart\Implementation
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PieChart implements PieChartInterface
{

    use ComponentHelper;

    /**
     * @var float|null
     */
    private $customTotalValue = null;
    /**
     * @var Section[]
     */
    private $sections = [];
    /**
     * @var bool
     */
    private $showLegend = true;
    /**
     * @var float
     */
    private $totalValue = 0;
    /**
     * @var bool
     */
    private $valuesInLegend = false;


    /**
     * PieChart constructor
     *
     * @param PieChartItemInterface[] $pieChartItems
     */
    public function __construct(array $pieChartItems)
    {
        if (count($pieChartItems) === 0) {
            throw new InvalidArgumentException(self::ERR_NO_ITEMS);
        } else {
            if (count($pieChartItems) > self::MAX_ITEMS) {
                throw new InvalidArgumentException(self::ERR_TOO_MANY_ITEMS);
            }
        }

        $this->calcTotalValue($pieChartItems);
        $this->createSections($pieChartItems);
    }


    /**
     * @inheritDoc
     */
    public function getCustomTotalValue() : /*?*/ float
    {
        return $this->customTotalValue;
    }


    /**
     * @inheritDoc
     */
    public function getSections() : array
    {
        return $this->sections;
    }


    /**
     * @inheritDoc
     */
    public function getTotalValue() : float
    {
        return $this->totalValue;
    }


    /**
     * @inheritDoc
     */
    public function isShowLegend() : bool
    {
        return $this->showLegend;
    }


    /**
     * @inheritDoc
     */
    public function isValuesInLegend() : bool
    {
        return $this->valuesInLegend;
    }


    /**
     * @inheritDoc
     */
    public function withCustomTotalValue(/*?*/ float $custom_total_value = null) : PieChartInterface
    {
        if (!is_null($custom_total_value)) {
            $this->checkFloatArg("custom_total_value", $custom_total_value);
        }
        $clone = clone $this;
        $clone->customTotalValue = $custom_total_value;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withShowLegend(bool $state) : PieChartInterface
    {
        //$this->checkBoolArg("state", $state);
        $clone = clone $this;
        $clone->showLegend = $state;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withValuesInLegend(bool $state) : PieChartInterface
    {
        //$this->checkBoolArg("state", $state);
        $clone = clone $this;
        $clone->valuesInLegend = $state;

        return $clone;
    }


    /**
     * @param PieChartItemInterface[] $pieChartItems
     */
    protected function calcTotalValue(array $pieChartItems)/*: void*/
    {
        $total = 0;
        foreach ($pieChartItems as $item) {
            $total += $item->getValue();
        }
        $this->totalValue = $total;
    }


    /**
     * @param PieChartItemInterface[] $pieChartItems
     */
    protected function createSections(array $pieChartItems)/*: void*/
    {
        $currentOffset = 0;
        $index = 1;

        foreach ($pieChartItems as $item) {
            $section = new Section($item, $this->totalValue, count($pieChartItems), $index, $currentOffset);
            $this->sections[] = $section;
            $currentOffset += $section->getStrokeLength();
            $index++;
        }
    }
}
