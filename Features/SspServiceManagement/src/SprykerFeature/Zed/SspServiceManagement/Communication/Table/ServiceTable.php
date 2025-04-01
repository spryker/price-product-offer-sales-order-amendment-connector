<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Communication\Table;

use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderItemMetadataTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderItemTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesOrderTableMap;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

class ServiceTable extends AbstractTable
{
    /**
     * @var string
     */
    protected const HEADER_ORDER_REFERENCE = 'Order Reference';

    /**
     * @var string
     */
    protected const HEADER_CUSTOMER = 'Customer';

    /**
     * @var string
     */
    protected const HEADER_COMPANY = 'Company';

    /**
     * @var string
     */
    protected const HEADER_SERVICE = 'Service';

    /**
     * @var string
     */
    protected const HEADER_TIME_AND_DATE = 'Time and Date';

    /**
     * @var string
     */
    protected const HEADER_CREATED_AT = 'Created At';

    /**
     * @var string
     */
    protected const COL_ORDER_REFERENCE = 'order_reference';

    /**
     * @var string
     */
    protected const COL_CUSTOMER = 'customer';

    /**
     * @var string
     */
    protected const COL_COMPANY = 'company';

    /**
     * @var string
     */
    protected const COL_SERVICE = 'service';

    /**
     * @var string
     */
    protected const COL_SCHEDULED_AT = 'scheduled_at';

    /**
     * @var string
     */
    protected const COL_CREATED_AT = 'created_at';

    /**
     * @var string
     */
    protected const COL_FIRST_NAME = 'first_name';

    /**
     * @var string
     */
    protected const COL_LAST_NAME = 'last_name';

    /**
     * @var string
     */
    protected const COL_ID_SALES_ORDER = 'id_sales_order';

    /**
     * @var string
     */
    protected const COL_ID_SALES_ORDER_ITEM = 'id_sales_order_item';

    /**
     * @uses \Spryker\Zed\Sales\Communication\Controller\DetailController::PARAM_ID_SALES_ORDER
     *
     * @var string
     */
    protected const REQUEST_PARAM_ID_SALES_ORDER = 'id-sales-order';

    /**
     * @uses \Spryker\Zed\Sales\Communication\Controller\DetailController::indexAction()
     *
     * @var string
     */
    protected const URL_SALES_ORDER_DETAIL_PAGE = '/sales/detail';

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery $salesOrderItemQuery
     */
    public function __construct(protected SpySalesOrderItemQuery $salesOrderItemQuery)
    {
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setHeader($this->getHeaders());
        $config->setSortable($this->getSortableColumns());
        $config->setSearchable($this->getSearchableColumns());
        $config->setRawColumns($this->getRawColumns());
        $config->setDefaultSortField(static::COL_SCHEDULED_AT);

        return $config;
    }

    /**
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            static::COL_ORDER_REFERENCE => static::HEADER_ORDER_REFERENCE,
            static::COL_CUSTOMER => static::HEADER_CUSTOMER,
            static::COL_COMPANY => static::HEADER_COMPANY,
            static::COL_SERVICE => static::HEADER_SERVICE,
            static::COL_SCHEDULED_AT => static::HEADER_TIME_AND_DATE,
            static::COL_CREATED_AT => static::HEADER_CREATED_AT,
        ];
    }

    /**
     * @return list<string>
     */
    protected function getSortableColumns(): array
    {
        return [
            static::COL_ORDER_REFERENCE,
            static::COL_SCHEDULED_AT,
        ];
    }

    /**
     * @return list<string>
     */
    protected function getSearchableColumns(): array
    {
        return [
            SpyCustomerTableMap::COL_FIRST_NAME,
            SpyCustomerTableMap::COL_LAST_NAME,
            SpyCompanyTableMap::COL_NAME,
            SpySalesOrderItemTableMap::COL_NAME,
        ];
    }

    /**
     * @return list<string>
     */
    protected function getRawColumns(): array
    {
        return [
            static::COL_ORDER_REFERENCE,
        ];
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array<array<string, mixed>>
     */
    protected function prepareData(TableConfiguration $config): array
    {
        $query = $this->prepareQuery();
        $queryResults = $this->runQuery($query, $config);

        $results = [];
        foreach ($queryResults as $item) {
            $results[] = $this->prepareTableRow($item);
        }

        return $results;
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery
     */
    protected function prepareQuery(): SpySalesOrderItemQuery
    {
        $query = $this->salesOrderItemQuery->useSpySalesOrderItemProductAbstractTypeExistsQuery()->endUse();

        // @phpstan-ignore-next-line
        return $this->joinOrderData($query);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery $query
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery
     */
    protected function joinOrderData(SpySalesOrderItemQuery $query): SpySalesOrderItemQuery
    {
        // @phpstan-ignore-next-line
        return $query
            ->useMetadataQuery(null, Criteria::LEFT_JOIN)
                ->withColumn(SpySalesOrderItemMetadataTableMap::COL_SCHEDULED_AT, static::COL_SCHEDULED_AT)
            ->endUse()
            ->useOrderQuery()
                ->withColumn(SpySalesOrderTableMap::COL_ORDER_REFERENCE, static::COL_ORDER_REFERENCE)
                ->withColumn(SpySalesOrderTableMap::COL_ID_SALES_ORDER, static::COL_ID_SALES_ORDER)
                ->addJoin(
                    SpySalesOrderTableMap::COL_CUSTOMER_REFERENCE,
                    SpyCustomerTableMap::COL_CUSTOMER_REFERENCE,
                    Criteria::LEFT_JOIN,
                )
                ->addJoin(
                    SpySalesOrderTableMap::COL_COMPANY_UUID,
                    SpyCompanyTableMap::COL_UUID,
                    Criteria::LEFT_JOIN,
                )
                ->withColumn(SpyCustomerTableMap::COL_FIRST_NAME, static::COL_FIRST_NAME)
                ->withColumn(SpyCustomerTableMap::COL_LAST_NAME, static::COL_LAST_NAME)
                ->withColumn(SpyCompanyTableMap::COL_NAME, static::COL_COMPANY)
            ->endUse()
            ->withColumn(SpySalesOrderItemTableMap::COL_NAME, static::COL_SERVICE)
            ->withColumn(SpySalesOrderItemTableMap::COL_ID_SALES_ORDER_ITEM, static::COL_ID_SALES_ORDER_ITEM)
            ->withColumn(SpySalesOrderItemTableMap::COL_CREATED_AT, static::COL_CREATED_AT);
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    protected function prepareTableRow(array $item): array
    {
        return [
            static::COL_ORDER_REFERENCE => $this->createOrderReferenceLink(
                $item[static::COL_ORDER_REFERENCE],
                $item[static::COL_ID_SALES_ORDER],
                $item[static::COL_ID_SALES_ORDER_ITEM],
            ),
            static::COL_CUSTOMER => $this->formatCustomerName(
                $item[static::COL_FIRST_NAME],
                $item[static::COL_LAST_NAME],
            ),
            static::COL_COMPANY => $item[static::COL_COMPANY],
            static::COL_SERVICE => $item[static::COL_SERVICE],
            static::COL_SCHEDULED_AT => $item[static::COL_SCHEDULED_AT],
            static::COL_CREATED_AT => $item[static::COL_CREATED_AT],
        ];
    }

    /**
     * @param string $orderReference
     * @param int $idSalesOrder
     * @param int $idSalesOrderItem
     *
     * @return string
     */
    protected function createOrderReferenceLink(string $orderReference, int $idSalesOrder, int $idSalesOrderItem): string
    {
        $url = Url::generate(
            static::URL_SALES_ORDER_DETAIL_PAGE,
            [static::REQUEST_PARAM_ID_SALES_ORDER => $idSalesOrder],
            [Url::FRAGMENT => sprintf('id-sales-order-item-%s', $idSalesOrderItem)],
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $url,
            $orderReference,
        );
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return string
     */
    protected function formatCustomerName(string $firstName, string $lastName): string
    {
        return sprintf('%s %s', $firstName, $lastName);
    }
}
