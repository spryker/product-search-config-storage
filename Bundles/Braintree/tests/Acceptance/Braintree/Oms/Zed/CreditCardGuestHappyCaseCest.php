<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Acceptance\Braintree\Oms\Zed;

use Acceptance\Braintree\Checkout\Yves\Tester\CheckoutTester;
use Acceptance\Braintree\Oms\Zed\Tester\OmsTester;

/**
 * @group Acceptance
 * @group Braintree
 * @group Oms
 * @group Zed
 * @group CreditCard
 * @group CreditCardGuestHappyCase
 */
class CreditCardGuestHappyCaseCest
{

    /**
     * @param \Acceptance\Braintree\Oms\Zed\Tester\OmsTester $i
     *
     * @return void
     */
    public function closeCreditCardOrderHappyCase(OmsTester $i)
    {
        $checkoutTester = $i->haveFriend('checkoutTester', CheckoutTester::class);
        $checkoutTester->does(function (CheckoutTester $i) {
            $i->addToCart('/en/samsung-gear-s2-79');
            $i->checkoutWithCreditCardAsGuest();
        });
        $checkoutTester->leave();
        $i->wait(10);

        $i->amZed();
        $i->amLoggedInUser();
        $i->closeCreditCardOrderHappyCase();
    }

}