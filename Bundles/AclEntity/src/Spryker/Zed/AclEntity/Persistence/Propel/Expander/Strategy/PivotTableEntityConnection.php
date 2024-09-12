<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclEntity\Persistence\Propel\Expander\Strategy;

use Generated\Shared\Transfer\AclEntityMetadataTransfer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\PropelQuery;
use Spryker\Zed\AclEntity\Persistence\Exception\JoinNotFoundException;

/**
 * @deprecated Use the combination of {@link \Spryker\Zed\AclEntity\Persistence\Propel\Expander\Strategy\ForeignKeyEntityConnection}
 * or {@link \Spryker\Zed\AclEntity\Persistence\Propel\Expander\Strategy\ReferenceColumnEntityConnection} instead.
 */
class PivotTableEntityConnection extends AbstractAclEntityConnection implements AclEntityConnectionInterface
{
    /**
     * @var string
     */
    protected const RELATION_TEMPLATE = '%s.%s';

    /**
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     *
     * @return bool
     */
    public function isSupported(AclEntityMetadataTransfer $aclEntityMetadataTransfer): bool
    {
        $parentConnectionMetadataTransfer = $aclEntityMetadataTransfer->getParentOrFail()->getConnectionOrFail();

        return $parentConnectionMetadataTransfer->getReferencedColumn() && $parentConnectionMetadataTransfer->getReference();
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface> $query
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     * @param string $joinType
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    public function joinRelation(
        ModelCriteria $query,
        AclEntityMetadataTransfer $aclEntityMetadataTransfer,
        string $joinType = Criteria::INNER_JOIN
    ): ModelCriteria {
        trigger_error($this->getDeprecationMessage(), E_USER_DEPRECATED);

        $query = $this->addJoinToPivotTable($query, $aclEntityMetadataTransfer, $joinType);

        return $this->addJoinToTargetTable($query, $aclEntityMetadataTransfer, $joinType);
    }

    /**
     * @return string
     */
    protected function getDeprecationMessage(): string
    {
        return sprintf(
            '[Spryker/AclEntity] %s is deprecated. Please configure your AclEntityMetadata by %s.',
            static::class,
            sprintf('%s, %s', ForeignKeyEntityConnection::class, ReferenceColumnEntityConnection::class),
        );
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface> $query
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     * @param string $joinType
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    protected function addJoinToPivotTable(
        ModelCriteria $query,
        AclEntityMetadataTransfer $aclEntityMetadataTransfer,
        string $joinType
    ): ModelCriteria {
        $relation = sprintf(
            static::RELATION_TEMPLATE,
            $this->getShortClassName($aclEntityMetadataTransfer->getEntityNameOrFail()),
            $this->getPivotTableRelationName($aclEntityMetadataTransfer),
        );

        return $query->join($relation, $joinType);
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface> $query
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     * @param string $joinType
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    protected function addJoinToTargetTable(
        ModelCriteria $query,
        AclEntityMetadataTransfer $aclEntityMetadataTransfer,
        string $joinType
    ): ModelCriteria {
        $relationName = sprintf(
            static::RELATION_TEMPLATE,
            $this->getPivotTableRelationName($aclEntityMetadataTransfer),
            $this->getTargetTableRelationName($aclEntityMetadataTransfer),
        );

        return $query->join($relationName, $joinType);
    }

    /**
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     *
     * @return string
     */
    protected function getPivotTableRelationName(AclEntityMetadataTransfer $aclEntityMetadataTransfer): string
    {
        $pivotTableEntity = $aclEntityMetadataTransfer
            ->getParentOrFail()
            ->getConnectionOrFail()
            ->getPivotEntityNameOrFail();
        $relationName = $this->getShortClassName($pivotTableEntity);

        $tableMap = PropelQuery::from($aclEntityMetadataTransfer->getEntityNameOrFail())->getTableMapOrFail();
        if ($tableMap->hasRelation($relationName)) {
            return $relationName;
        }

        foreach ($tableMap->getRelations() as $relationMap) {
            if ($relationMap->getRightTable()->getPhpName() === $relationName) {
                return $relationMap->getName();
            }
        }

        return $relationName;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria<\Propel\Runtime\ActiveRecord\ActiveRecordInterface> $query
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     * @param string $joinType
     *
     * @throws \Spryker\Zed\AclEntity\Persistence\Exception\JoinNotFoundException
     *
     * @return \Propel\Runtime\ActiveQuery\Join
     */
    protected function generateAclEntityJoin(
        ModelCriteria $query,
        AclEntityMetadataTransfer $aclEntityMetadataTransfer,
        string $joinType
    ): Join {
        $query = $this->addJoinToPivotTable($query, $aclEntityMetadataTransfer, $joinType);
        $query = $this->addJoinToTargetTable($query, $aclEntityMetadataTransfer, $joinType);

        $tableName = $this->getTableMapByEntityClass(
            $aclEntityMetadataTransfer->getParentOrFail()->getEntityNameOrFail(),
        )->getNameOrFail();

        $join = $this->joinMatcher->matchOneByRightTableName(
            $tableName,
            $query->getJoins(),
        );

        if (!$join) {
            throw new JoinNotFoundException($query, $tableName);
        }

        return $join;
    }

    /**
     * @param \Generated\Shared\Transfer\AclEntityMetadataTransfer $aclEntityMetadataTransfer
     *
     * @return string
     */
    protected function getTargetTableRelationName(AclEntityMetadataTransfer $aclEntityMetadataTransfer): string
    {
        $targetTableEntity = $aclEntityMetadataTransfer
            ->getParentOrFail()
            ->getEntityNameOrFail();
        $relationName = $this->getShortClassName($targetTableEntity);

        $pivotQuery = PropelQuery::from(
            $aclEntityMetadataTransfer->getParentOrFail()->getConnectionOrFail()->getPivotEntityNameOrFail(),
        );
        $pivotTableMap = $pivotQuery->getTableMapOrFail();
        if ($pivotTableMap->hasRelation($relationName)) {
            return $relationName;
        }
        foreach ($pivotTableMap->getRelations() as $relationMap) {
            if ($relationMap->getRightTable()->getPhpName() === $relationName) {
                return $relationMap->getName();
            }
        }

        return $relationName;
    }
}
