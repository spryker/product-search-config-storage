<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductSearchConfigStorage\Communication\Plugin\Event\Listener;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\ProductSearchConfigStorage\Persistence\ProductSearchConfigStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductSearchConfigStorage\Communication\ProductSearchConfigStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductSearchConfigStorage\Business\ProductSearchConfigStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductSearchConfigStorage\ProductSearchConfigStorageConfig getConfig()
 */
class ProductSearchConfigStoragePublishListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $this->getFacade()->publish();
    }
}
