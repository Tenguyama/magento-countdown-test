<?php

namespace Kutsa\Countdown\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use DateTime;

class CountdownTimer extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * CountdownTimer constructor
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        RuleCollectionFactory $ruleCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Calculate end date and return debug info.
     *
     * @return array [endDate, debugInfo]
     */
    public function calculateEndDate()
    {
        $product = $this->getProduct();
        $debugInfo = [];
        if (!$product) {
            $debugInfo[] = '<p>product not found</p>';
            return [null, $debugInfo];
        }

        $specialToDate = $product->getData('special_to_date');
        $debugInfo[] = '<p>Special price end date: ' . ($specialToDate ?: 'none') . '</p>';

        $catalogRuleCollection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter('is_active', 1) // беру лиш активні правила
            ->addFieldToFilter('to_date', ['neq' => null])// беру правила лиш з ненульовою датою
            ->setOrder('to_date', 'ASC');// сортую за полем 'to_date' по зростанню (найближчий термін закінчення буде першим)


        $catalogRuleToDate = null;
        $ruleName = '';

        foreach ($catalogRuleCollection as $rule) {
            // Перевірка, чи застосовується правило до товару
            if ($rule->getConditions()->validate($product)) {
                /*
                 * так як я відсортував всю колекцію по даті,
                 * то виконую цикл тільки поки не знайду перше правило
                 * яке застосується для товару
                 * далі виходжу з циклу
                 */
                //$ruleName = $rule->getData('name');
                $ruleName = $rule->getName();
                $catalogRuleToDate = $rule->getToDate();
                break;
            }
        }

        $debugInfo[] = '<p>Catalog rule end date: ' . ($catalogRuleToDate ?: 'none') . '</p>';
        $debugInfo[] = '<p>Catalog rule name: ' . ($ruleName ?: 'none') . '</p>';

        $endDate = null;
        if ($specialToDate && $catalogRuleToDate) {
            $specialPriceDate = new DateTime($specialToDate);
            $catalogRuleDate = new DateTime($catalogRuleToDate);

            if ($specialPriceDate < $catalogRuleDate) {
                $endDate = $specialToDate;
                $debugInfo[] = '<p>endDate is specialToDate</p>';
            } else {
                $endDate = $catalogRuleToDate;
                $debugInfo[] = '<p>endDate is catalogRuleToDate</p>';
            }
        } elseif ($specialToDate) {
            $endDate = $specialToDate;
            $debugInfo[] = '<p>endDate is specialToDate</p>';
        } elseif ($catalogRuleToDate) {
            $endDate = $catalogRuleToDate;
            $debugInfo[] = '<p>endDate is catalogRuleToDate</p>';
        } else {
            $debugInfo[] = '<p>endDate not found</p>';
        }

        return [$endDate, $debugInfo];
    }
}
