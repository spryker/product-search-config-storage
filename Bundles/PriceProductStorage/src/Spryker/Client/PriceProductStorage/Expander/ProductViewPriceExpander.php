<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProductStorage\Expander;

use Generated\Shared\Transfer\PriceProductStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\PriceProductStorage\Dependency\Client\PriceProductStorageToPriceProductInterface;
use Spryker\Client\PriceProductStorage\Storage\PriceAbstractStorageReaderInterface;
use Spryker\Client\PriceProductStorage\Storage\PriceConcreteStorageReaderInterface;

class ProductViewPriceExpander implements ProductViewPriceExpanderInterface
{
    /**
     * @var PriceAbstractStorageReaderInterface
     */
    protected $priceAbstractStorageReader;

    /**
     * @var PriceConcreteStorageReaderInterface
     */
    protected $priceConcreteStorageReader;

    /**
     * @var PriceProductStorageToPriceProductInterface
     */
    protected $priceProductClient;

    /**
     * @param PriceAbstractStorageReaderInterface $priceAbstractStorageReader
     * @param PriceConcreteStorageReaderInterface $priceConcreteStorageReader
     * @param PriceProductStorageToPriceProductInterface $priceProductClient
     */
    public function __construct(
        PriceAbstractStorageReaderInterface $priceAbstractStorageReader,
        PriceConcreteStorageReaderInterface $priceConcreteStorageReader,
        PriceProductStorageToPriceProductInterface $priceProductClient
    )
    {
        $this->priceAbstractStorageReader = $priceAbstractStorageReader;
        $this->priceConcreteStorageReader = $priceConcreteStorageReader;
        $this->priceProductClient = $priceProductClient;
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     *
     * @return ProductViewTransfer
     */
    public function expandProductViewPriceData(ProductViewTransfer $productViewTransfer)
    {
        $productViewPriceData = $this->getProductViewPrices($productViewTransfer);
        $currentProductPriceTransfer = $this->priceProductClient->resolveProductPrice(
            $productViewPriceData
        );

        $productViewTransfer->setPrices($currentProductPriceTransfer->getPrices());
        $productViewTransfer->setPrice($currentProductPriceTransfer->getPrice());

        return $productViewTransfer;
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     *
     * @return array
     */
    protected function getProductViewPrices(ProductViewTransfer $productViewTransfer)
    {
        $priceProductAbstractStorageTransfer = $this->getPriceAbstractData($productViewTransfer);
        if (!$priceProductAbstractStorageTransfer) {
            return [];
        }

        $priceProductConcreteStorageTransfer = $this->getPriceConcreteData($productViewTransfer);
        if (!$priceProductConcreteStorageTransfer) {
            return $priceProductAbstractStorageTransfer->getPrices();
        }

        $productViewPriceData = array_replace_recursive(
            $priceProductAbstractStorageTransfer->getPrices(),
            $priceProductConcreteStorageTransfer->getPrices()
        );

        return $productViewPriceData;
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     *
     * @return PriceProductStorageTransfer|null
     */
    protected function getPriceAbstractData(ProductViewTransfer $productViewTransfer)
    {
        return $this->priceAbstractStorageReader->findPriceAbstractStorageTransfer($productViewTransfer->getIdProductAbstract());
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     *
     * @return PriceProductStorageTransfer|null
     */
    protected function getPriceConcreteData(ProductViewTransfer $productViewTransfer)
    {
        if (!$productViewTransfer->getIdProductConcrete()) {
            return null;
        }

        return $this->priceConcreteStorageReader->findPriceConcreteStorageTransfer($productViewTransfer->getIdProductConcrete());
    }
}
