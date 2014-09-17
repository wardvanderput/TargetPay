<?php
namespace TargetPay;

/**
 * Bancontact/Mister Cash
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.0.1
 */
class MisterCash extends AbstractPayment
{
    /**
     * @type integer MINIMUM_AMOUNT Minimum amount in cents (EUR 0.49).
     * @type integer MAXIMUM_AMOUNT Maximum amount in cents (EUR 5,000.00).
     */
    const MINIMUM_AMOUNT = 49;
    const MAXIMUM_AMOUNT = 500000;

    /** @type string $BaseRequest */
    protected $BaseRequest = 'https://www.targetpay.com/mrcash/start';
}
