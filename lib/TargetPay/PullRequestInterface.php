<?php
namespace TargetPay;

/**
 * Pull Request Interface and Request Types
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.0
 *
 * @api
 *
 * @used-by \TargetPay\PullRequest::__construct()
 */
interface PullRequestInterface
{
    /**
     * Constants
     *
     * @type string IDEAL          Validation URL for iDEAL.
     * @type string MISTER_CASH    Validation URL for Bancontact/Mister Cash.
     * @type string PAYSAFECARD    Validation URL for Paysafecard.
     * @type string SOFORT_BANKING Validation URL for SOFORT Banking.
     */
    const IDEAL          = 'https://www.targetpay.com/ideal/check?';
    const MISTER_CASH    = 'https://www.targetpay.com/mrcash/check?';
    const PAYSAFECARD    = 'https://www.targetpay.com/paysafecard/check?';
    const SOFORT_BANKING = 'https://www.targetpay.com/directebanking/check?';

    /**
     * Aliases
     *
     * Although the use of aliases listed below is discouraged, the names of
     * payment methods and payment providers can and presumably WILL change.
     *
     * @type string BANCONTACT_MISTER_CASH Alias of MISTER_CASH.
     * @type string DIRECT_EBANKING        Alias of SOFORT_BANKING.
     * @type string DIRECTEBANKING         Alias of SOFORT_BANKING.
     * @type string MISTERCASH             Alias of MISTER_CASH.
     * @type string MR_CASH                Alias of MISTER_CASH.
     * @type string MRCASH                 Alias of MISTER_CASH.
     * @type string SOFORTBANKING          Alias of SOFORT_BANKING.
     * @type string SOFORTUBERWEISUNG      Alias of SOFORT_BANKING.
     * @type string WALLIE                 Alias of PAYSAFECARD.
     * @type string WALLIE_CARD            Alias of PAYSAFECARD.
     */
    const BANCONTACT_MISTER_CASH = 'https://www.targetpay.com/mrcash/check?';
    const DIRECT_EBANKING        = 'https://www.targetpay.com/directebanking/check?';
    const DIRECTEBANKING         = 'https://www.targetpay.com/directebanking/check?';
    const MISTERCASH             = 'https://www.targetpay.com/mrcash/check?';
    const MR_CASH                = 'https://www.targetpay.com/mrcash/check?';
    const MRCASH                 = 'https://www.targetpay.com/mrcash/check?';
    const SOFORTBANKING          = 'https://www.targetpay.com/directebanking/check?';
    const SOFORTUBERWEISUNG      = 'https://www.targetpay.com/directebanking/check?';
    const WALLIE                 = 'https://www.targetpay.com/paysafecard/check?';
    const WALLIE_CARD            = 'https://www.targetpay.com/paysafecard/check?';

    /**
     * @param void
     * @return string
     */
    public function getRequest();

    /**
     * @param void
     * @return string
     */
    public function getResponse();

    /**
     * @param void
     * @return boolean
     */
    public function validate();
}
