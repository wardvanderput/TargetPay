<?php
namespace TargetPay;

/**
 * SOFORT Banking
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.0.1
 */
class SOFORTBanking extends AbstractPayment
{
    /**
     * @type integer MINIMUM_AMOUNT Minimum amount in cents (EUR 0.49).
     * @type integer MAXIMUM_AMOUNT Maximum amount in cents (EUR 5,000.00).
     */
    const MINIMUM_AMOUNT = 49;
    const MAXIMUM_AMOUNT = 500000;

    /**
     * @type string $BaseRequest URI base to start a SOFORT Banking transaction.
     *     Note that the TargetPay API uses the /directebanking/ directory for
     *     DIRECTebanking.
     */
    protected $BaseRequest = 'https://www.targetpay.com/directebanking/start';

    /**
     * Set the country.
     *
     * @param string|integer $country_id ISO country identifier.  Three types
     *     of ISO 3166-1 country codes are supported: alpha-2 codes (like 'DE'
     *     for Germany), alpha-3 codes (like 'DEU' for Germany), and numeric
     *     codes (like '276' for Germany).
     *
     * @return void
     *
     * @throws \InvalidArgumentException Throws an SPL (Standard PHP Library)
     *     invalid argument exception if the country ID is not an integer or
     *     string.
     *
     * @throws \DomainException Throws TargetPay error TP0008 as an SPL domain
     *     exception if the country is not supported for SOFORT Banking.
     *     Unfortunately, TargetPay currently do not support all SOFORT Banking
     *     countries.
     */
    public function setCountry($country_id)
    {
        if (is_int($country_id)) {
            $country_id = (string) $country_id;
        }
        if (is_string($country_id)) {
            $country_id = strtoupper($country_id);
        } else {
            throw new \InvalidArgumentException(
                'setCountry() expects parameter 1 to be an integer or string, '
                . gettype($country_id) . ' given');
        }

        $countries = array(
            '32' => 32, 'BE' => 32, 'BEL' => 32, '056' => 32,  // Belgium
            '41' => 41, 'CH' => 41, 'CHE' => 41, '756' => 41,  // Switzerland
            '43' => 43, 'AT' => 43, 'AUT' => 43, '040' => 43,  // Austria
            '49' => 49, 'DE' => 49, 'DEU' => 49, '276' => 49,  // Germany
        );
        if (array_key_exists($country_id, $countries)) {
            $this->BaseRequestParameters['country'] = $countries[$country_id];
        } else {
            throw new \DomainException(
                'TP0008 Country not supported for SOFORT Banking',
                (int) base_convert('TP0008', 36, 10)
            );
        }
    }
}
