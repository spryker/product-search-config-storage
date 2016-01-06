<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Client\Sales;

use Generated\Shared\Transfer\OrderListTransfer;
use Generated\Shared\Transfer\OrderTransfer;

interface SalesClientInterface
{

    /**
     * @param OrderListTransfer $orderListTransfer
     *
     * @return OrderListTransfer
     */
    public function getOrders(OrderListTransfer $orderListTransfer);

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return mixed
     */
    public function getOrderDetails(OrderTransfer $orderTransfer);

}