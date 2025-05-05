<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspFileManagement\Communication\Table;

use Generated\Shared\Transfer\SspAssetTransfer;
use Orm\Zed\FileManager\Persistence\Map\SpyFileInfoTableMap;
use Orm\Zed\FileManager\Persistence\Map\SpyFileTableMap;
use Orm\Zed\FileManager\Persistence\SpyFileQuery;
use Orm\Zed\SspFileManagement\Persistence\Map\SpySspAssetFileTableMap;
use Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use SprykerFeature\Zed\SspFileManagement\Communication\Controller\AbstractController;

class AttachedSspAssetFileTable extends AbstractTable
{
    /**
     * @var string
     */
    protected const BASE_URL = '/ssp-file-management/attached-ssp-asset-file-list';

    /**
     * @var string
     */
    public const PARAM_ID_FILE = 'id-file';

    /**
     * @var string
     */
    public const PARAM_ID_SSP_ASSET = 'id-ssp-asset';

    /**
     * @var string
     */
    protected const URL_SSP_FILE_DETAIL = '/ssp-file-management/view';

    /**
     * @var string
     */
    protected const BUTTON_VIEW = 'View';

    /**
     * @var string
     */
    protected const COL_ACTIONS = 'actions';

    /**
     * @var string
     */
    protected const COL_CREATED_AT = 'created_at';

    /**
     * @var string
     */
    protected const HEADER_FILE_NAME = 'File Name';

    /**
     * @var string
     */
    protected const HEADER_FILE_REFERENCE = 'Reference';

    /**
     * @var string
     */
    protected const HEADER_TYPE = 'Type';

    /**
     * @var string
     */
    protected const HEADER_DATE_ADDED = 'Date Added';

    /**
     * @var string
     */
    protected const COL_SIZE = 'size';

    /**
     * @var string
     */
    protected const COL_TYPE = 'type';

    /**
     * @var string
     */
    protected const COL_ID_FILE_INFO = 'id_file_info';

    /**
     * @var string
     */
    protected const REQUEST_ID_FILE_INFO = 'id-file-info';

    /**
     * @param \Generated\Shared\Transfer\SspAssetTransfer $sspAssetTransfer
     * @param \Orm\Zed\FileManager\Persistence\SpyFileQuery $fileQuery
     * @param \Spryker\Service\UtilDateTime\UtilDateTimeServiceInterface $utilDateTimeService
     */
    public function __construct(
        protected SspAssetTransfer $sspAssetTransfer,
        protected SpyFileQuery $fileQuery,
        protected UtilDateTimeServiceInterface $utilDateTimeService
    ) {
        $this->baseUrl = static::BASE_URL;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config): TableConfiguration
    {
        $config->setUrl(sprintf('table?%s=%s', static::PARAM_ID_SSP_ASSET, $this->sspAssetTransfer->getIdSspAsset()));

        $config = $this->setHeader($config);

        $config->setRawColumns([
            static::COL_ACTIONS,
        ]);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function setHeader(TableConfiguration $config): TableConfiguration
    {
        $baseData = [
            SpyFileTableMap::COL_FILE_NAME => static::HEADER_FILE_NAME,
            static::COL_CREATED_AT => static::HEADER_DATE_ADDED,
            static::COL_TYPE => static::HEADER_TYPE,
            static::COL_ACTIONS => static::COL_ACTIONS,
        ];

        $config->setHeader($baseData);

        return $config;
    }

    /**
     * @return \Orm\Zed\FileManager\Persistence\SpyFileQuery
     */
    protected function prepareQuery(): SpyFileQuery
    {
        $fileQuery = $this->fileQuery->innerJoinSpyFileInfo()
            ->withColumn(SpyFileInfoTableMap::COL_SIZE, static::COL_SIZE)
            ->withColumn(SpyFileInfoTableMap::COL_EXTENSION, static::COL_TYPE)
            ->withColumn(SpyFileInfoTableMap::COL_ID_FILE_INFO, static::COL_ID_FILE_INFO)
            ->withColumn(SpySspAssetFileTableMap::COL_CREATED_AT, static::COL_CREATED_AT);

        $fileQuery->useSpySspAssetFileQuery()
                ->filterByFkSspAsset($this->sspAssetTransfer->getIdSspAsset())
            ->endUse();

        return $fileQuery;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array<int, array<string, mixed>>
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
     * @param array<mixed> $item
     *
     * @return array<mixed>
     */
    protected function prepareTableRow(array $item): array
    {
        return [
            SpyFileTableMap::COL_FILE_NAME => $item[SpyFileTableMap::COL_FILE_NAME],
            static::COL_CREATED_AT => $this->utilDateTimeService->formatDateTime($item[static::COL_CREATED_AT]),
            static::COL_TYPE => $item[static::COL_TYPE],
            static::COL_ACTIONS => $this->buildLinks($item),
        ];
    }

    /**
     * @param array<mixed> $fileData
     *
     * @return string
     */
    protected function buildLinks(array $fileData): string
    {
        $buttons = [];

        $buttons[] = $this->generateViewButton(
            Url::generate(static::URL_SSP_FILE_DETAIL, [static::PARAM_ID_FILE => $fileData[SpyFileTableMap::COL_ID_FILE]]),
            static::BUTTON_VIEW,
        );

        $buttons[] = $this->generateViewButton(
            Url::generate(AbstractController::URL_FILE_MANAGER_GUI_DOWNLOAD_FILE, [
                static::REQUEST_ID_FILE_INFO => $fileData[static::COL_ID_FILE_INFO],
            ]),
            'Download',
        );

        return implode(' ', $buttons);
    }
}
