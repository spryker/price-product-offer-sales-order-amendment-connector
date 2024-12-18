<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Table;

use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Orm\Zed\SalesReturn\Persistence\Map\SpySalesReturnItemTableMap;
use Orm\Zed\SalesReturn\Persistence\Map\SpySalesReturnTableMap;
use Orm\Zed\SalesReturn\Persistence\SpySalesReturn;
use Orm\Zed\SalesReturn\Persistence\SpySalesReturnQuery;
use Orm\Zed\StateMachine\Persistence\Map\SpyStateMachineItemStateTableMap;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantUserFacadeInterface;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Service\MerchantSalesReturnMerchantUserGuiToUtilDateTimeServiceInterface;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\MerchantSalesReturnMerchantUserGuiConfig;
use Spryker\Zed\MerchantUser\Business\Exception\CurrentMerchantUserNotFoundException;

class MyReturnsTable extends AbstractTable
{
    /**
     * @var string
     */
    protected const COL_RETURN_ID = 'id_sales_return';

    /**
     * @var string
     */
    protected const COL_RETURN_REFERENCE = 'return_reference';

    /**
     * @var string
     */
    protected const COL_MARKETPLACE_ORDER_REFERENCE = 'order_reference';

    /**
     * @var string
     */
    protected const COL_RETURNED_PRODUCTS = 'returned_products';

    /**
     * @var string
     */
    protected const COL_MERCHANT_REFERENCE = 'merchant_reference';

    /**
     * @var string
     */
    protected const COL_RETURN_DATE = 'created_at';

    /**
     * @var string
     */
    protected const COL_STATE = 'state';

    /**
     * @var string
     */
    protected const COL_ACTIONS = 'actions';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Controller\DetailController::indexAction()
     *
     * @var string
     */
    protected const ROUTE_DETAIL = '/merchant-sales-return-merchant-user-gui/detail';

    /**
     * @uses \Spryker\Zed\SalesReturnGui\Communication\Controller\ReturnSlipController::indexAction()
     *
     * @var string
     */
    protected const ROUTE_RETURN_SLIP = '/sales-return-gui/return-slip';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Controller\DetailController::PARAM_ID_RETURN
     *
     * @var string
     */
    protected const PARAM_ID_RETURN = 'id-return';

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Service\MerchantSalesReturnMerchantUserGuiToUtilDateTimeServiceInterface
     */
    protected $utilDateTimeService;

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\MerchantSalesReturnMerchantUserGuiConfig
     */
    protected $merchantSalesReturnMerchantUserGuiConfig;

    /**
     * @var \Orm\Zed\SalesReturn\Persistence\SpySalesReturnQuery
     */
    protected $salesReturnQuery;

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantUserFacadeInterface
     */
    protected $merchantUserFacade;

    /**
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Service\MerchantSalesReturnMerchantUserGuiToUtilDateTimeServiceInterface $utilDateTimeService
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\MerchantSalesReturnMerchantUserGuiConfig $merchantSalesReturnMerchantUserGuiConfig
     * @param \Orm\Zed\SalesReturn\Persistence\SpySalesReturnQuery $salesReturnQuery
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantUserFacadeInterface $merchantUserFacade
     */
    public function __construct(
        MerchantSalesReturnMerchantUserGuiToUtilDateTimeServiceInterface $utilDateTimeService,
        MerchantSalesReturnMerchantUserGuiConfig $merchantSalesReturnMerchantUserGuiConfig,
        SpySalesReturnQuery $salesReturnQuery,
        MerchantSalesReturnMerchantUserGuiToMerchantUserFacadeInterface $merchantUserFacade
    ) {
        $this->utilDateTimeService = $utilDateTimeService;
        $this->salesReturnQuery = $salesReturnQuery;
        $this->merchantSalesReturnMerchantUserGuiConfig = $merchantSalesReturnMerchantUserGuiConfig;
        $this->merchantUserFacade = $merchantUserFacade;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader([
            static::COL_RETURN_ID => 'Return ID',
            static::COL_RETURN_REFERENCE => 'Return Reference',
            static::COL_MERCHANT_REFERENCE => 'Merchant Reference',
            static::COL_MARKETPLACE_ORDER_REFERENCE => 'Marketplace Order Reference',
            static::COL_RETURNED_PRODUCTS => 'Returned Products',
            static::COL_RETURN_DATE => 'Return Date',
            static::COL_STATE => 'State',
            static::COL_ACTIONS => 'Actions',
        ]);

        $config->setRawColumns([
            static::COL_STATE,
            static::COL_ACTIONS,
        ]);

        $config->setSortable([
            static::COL_RETURN_ID,
            static::COL_RETURN_REFERENCE,
            static::COL_RETURN_DATE,
        ]);

        $config->setSearchable([
            SpySalesReturnTableMap::COL_ID_SALES_RETURN,
            SpySalesReturnTableMap::COL_RETURN_REFERENCE,
            SpySalesOrderTableMap::COL_ORDER_REFERENCE,
            SpySalesReturnTableMap::COL_MERCHANT_REFERENCE,
        ]);

        $config->setHasSearchableFieldsWithAggregateFunctions(true);
        $config->setDefaultSortField(static::COL_RETURN_ID, TableConfiguration::SORT_DESC);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array<array<int|string|null>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $query = $this->prepareQuery();

        if (!$query) {
            return [];
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\SalesReturn\Persistence\SpySalesReturn> $salesReturnEntityCollection */
        $salesReturnEntityCollection = $this->runQuery(
            $query,
            $config,
            true,
        );

        if (!$salesReturnEntityCollection->count()) {
            return [];
        }

        $returns = $this->mapReturns($salesReturnEntityCollection);

        return $this->expandReturnsWithItemStates($returns);
    }

    /**
     * @return \Orm\Zed\SalesReturn\Persistence\SpySalesReturnQuery|null
     */
    protected function prepareQuery(): ?SpySalesReturnQuery
    {
        try {
            $merchantTransfer = $this->merchantUserFacade
                ->getCurrentMerchantUser()
                ->getMerchant();
        } catch (CurrentMerchantUserNotFoundException $currentMerchantUserNotFoundException) {
            return null;
        }

        if (!$merchantTransfer) {
            return null;
        }

        $merchantReference = $merchantTransfer
            ->requireMerchantReference()
            ->getMerchantReference();

        /** @var \Orm\Zed\SalesReturn\Persistence\SpySalesReturnQuery $salesReturnQuery */
        $salesReturnQuery = $this->salesReturnQuery
            ->groupByIdSalesReturn()
            ->addGroupByColumn(SpySalesOrderTableMap::COL_ORDER_REFERENCE)
            ->useSpySalesReturnItemQuery()
                ->useSpySalesOrderItemQuery()
                    ->filterByMerchantReference($merchantReference)
                    ->joinOrder()
                ->endUse()
            ->endUse()
            ->withColumn(
                sprintf('COUNT(%s)', SpySalesReturnItemTableMap::COL_ID_SALES_RETURN_ITEM),
                static::COL_RETURNED_PRODUCTS,
            )
            ->withColumn(
                sprintf('GROUP_CONCAT(DISTINCT %s)', SpySalesOrderTableMap::COL_ORDER_REFERENCE),
                static::COL_MARKETPLACE_ORDER_REFERENCE,
            );

        return $salesReturnQuery;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\SalesReturn\Persistence\SpySalesReturn> $salesReturnEntityCollection
     *
     * @return array<array<string|int|null>>
     */
    protected function mapReturns(ObjectCollection $salesReturnEntityCollection): array
    {
        $returns = [];

        foreach ($salesReturnEntityCollection as $salesReturnEntity) {
            $createdAt = $salesReturnEntity->getCreatedAt() ?? '';

            $returnData = $salesReturnEntity->toArray();
            $returnData[static::COL_RETURN_ID] = $this->formatInt($returnData[static::COL_RETURN_ID]);
            $returnData[static::COL_RETURN_DATE] = $this->utilDateTimeService->formatDateTime($createdAt);
            $returnData[static::COL_ACTIONS] = $this->buildLinks($salesReturnEntity);
            $returnData[static::COL_RETURNED_PRODUCTS] = $this->formatInt($returnData[static::COL_RETURNED_PRODUCTS]);

            $returns[] = $returnData;
        }

        return $returns;
    }

    /**
     * @param array<array<int|string|null>> $returns
     *
     * @return array<array<int|string|null>>
     */
    protected function expandReturnsWithItemStates(array $returns): array
    {
        foreach ($returns as $index => $return) {
            $idSalesReturn = (int)$return[static::COL_RETURN_ID];
            $returns[$index][static::COL_STATE] = implode(' ', $this->getItemStateLabelsByIdSalesReturn($idSalesReturn));
        }

        return $returns;
    }

    /**
     * @param int $idSalesReturn
     *
     * @return array<string>
     */
    protected function getItemStateLabelsByIdSalesReturn(int $idSalesReturn): array
    {
        /** @var \Propel\Runtime\Collection\ArrayCollection $states */
        $states = $this->salesReturnQuery
            ->clear()
            ->filterByIdSalesReturn($idSalesReturn)
            ->useSpySalesReturnItemQuery()
                ->useSpySalesOrderItemQuery()
                    ->useMerchantSalesOrderItemQuery()
                        ->joinWithStateMachineItemState()
                        ->withColumn(
                            sprintf('GROUP_CONCAT(DISTINCT %s)', SpyStateMachineItemStateTableMap::COL_NAME),
                            static::COL_STATE,
                        )
                    ->endUse()
                ->endUse()
            ->endUse()
            ->select(static::COL_STATE)
            ->find();

        $stateLabels = [];

        foreach ($states->toArray() as $state) {
            $stateLabels[] = $this->generateLabel(ucfirst($state), $this->merchantSalesReturnMerchantUserGuiConfig->getItemStateToLabelClassMapping()[$state] ?? 'label-default');
        }

        return $stateLabels;
    }

    /**
     * @param \Orm\Zed\SalesReturn\Persistence\SpySalesReturn $salesReturnEntity
     *
     * @return string
     */
    protected function buildLinks(SpySalesReturn $salesReturnEntity): string
    {
        $buttons = [];

        $buttons[] = $this->generateViewButton(
            Url::generate(static::ROUTE_DETAIL, [
                static::PARAM_ID_RETURN => $salesReturnEntity->getIdSalesReturn(),
            ]),
            'View',
        );

        $buttons[] = $this->generateViewButton(
            Url::generate(static::ROUTE_RETURN_SLIP, [
                static::PARAM_ID_RETURN => $salesReturnEntity->getIdSalesReturn(),
            ]),
            'Print Slip',
            [
                'icon' => '',
                'class' => 'btn-create',
            ],
        );

        return implode(' ', $buttons);
    }
}
