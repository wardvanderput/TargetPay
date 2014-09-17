<?php
namespace TargetPay;

/**
 * Abstract TargetPay Payments Class
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.0
 */
abstract class AbstractPayment
{
    /**
     * Amounts
     *
     * All amounts are handled in cents (not euro's) and therefore as integers.
     * Child classes SHOULD set different minimum and maximum amounts
     * whenever they apply to a payment method.  The base minimum is set to
     * the iDEAL minimum amount (EUR 0.84) and the base maximum to the
     * Bancontact/Mister Cash and DIRECTebanking maximum (EUR 5,000.00).
     *
     * @type integer MINIMUM_AMOUNT Minimum amount in cents.
     * @type integer MAXIMUM_AMOUNT Maximum amount in cents.
     */
    const MINIMUM_AMOUNT = 84;
    const MAXIMUM_AMOUNT = 500000;

    /**
     * @type string $BaseRequest TargetPay URL to start a transaction.
     * @type array $BaseRequestParameters Request parameters.
     */
    protected $BaseRequest;
    protected $BaseRequestParameters = array();

    /**
     * Payment Constructor
     *
     * All TargetPay payment transactions MUST be started by providing a
     * personal sub-account layout code or "rtlo" in a request URL.
     * You have to set up a TargetPay account in order to process online
     * payments through the TargetPay API:
     *
     * @link https://www.targetpay.com/quickreg/69391
     *
     * @param integer $rtlo TargetPay sub-account layout code (rtlo).
     */
    final public function __construct($rtlo)
    {
        $this->setRtlo($rtlo);
    }

    /**
     * Get current transaction amount.
     *
     * @param void
     *
     * @return integer|null Amount payable in cents.
     */
    public function getAmount()
    {
        if (isset($this->BaseRequestParameters['amount'])) {
            return $this->BaseRequestParameters['amount'];
        } else {
            return null;
        }
    }

    /**
     * Get the current currency.
     *
     * @param void
     *
     * @return string ISO currency code.  Defaults to 'EUR' for the euro
     *     because TargetPay will handle payments in the euro by default
     *     if no currency is set.
     */
    public function getCurrency()
    {
        if (isset($this->BaseRequestParameters['currency'])) {
            return $this->BaseRequestParameters['currency'];
        } else {
            return 'EUR';
        }
    }

    /**
     * Get the TargetPay API request.
     *
     * @param void
     *
     * @return string TargetPay base request with request parameters.
     */
    public function getRequest()
    {
        $uri = rtrim($this->BaseRequest, '?') . '?';
        $params = $this->BaseRequestParameters;
        foreach ($params as $param => $value) {
            $uri .= $param . '=' . urlencode($value) . '&';
        }
        return rtrim($uri, '&');
    }

    /**
     * Get the rtlo.
     *
     * @param void
     *
     * @return integer TargetPay sub-account layout code (rtlo).
     */
    public function getRtlo()
    {
        return $this->BaseRequestParameters['rtlo'];
    }

    /**
     * Set the amount.
     *
     * @api
     *
     * @param integer $amount Transaction amount in cents.
     *
     * @throws \InvalidArgumentException Throws an SPL invalid argument
     *     exception if the amount is not an integer.
     *
     * @throws \DomainException Throws TargetPay error TP0002 or TP0003 as an
     *     SPL domain exception if the amount is too low (TP0002) or too high
     *     (TP0003) for the current payment method.
     */
    final public function setAmount($amount)
    {
        $amount = filter_var($amount, FILTER_VALIDATE_INT);
        if ($amount == false) {
            throw new \InvalidArgumentException('setAmount() expects parameter 1 to be an integer for an ammount in cents, '
                . gettype($amount) . ' given');
        } else {
            $amount = (int) $amount;
        }
        if ($amount < static::MINIMUM_AMOUNT) {
            throw new \DomainException('TP0002 Amount too low', (int) base_convert('TP0002', 36, 10));
        } elseif ($amount > static::MAXIMUM_AMOUNT) {
            throw new \DomainException('TP0003 Amount too high', (int) base_convert('TP0003', 36, 10));
        } else {
            $this->BaseRequestParameters['amount'] = $amount;
        }
    }

    /**
     * Set the currency.
     *
     * @api
     *
     * @param string $currency_code ISO 4217 currency code.  Defaults to 'EUR'
     *     for the European euro.
     */
    public function setCurrency($currency_code = 'EUR')
    {
        if (is_string($currency_code)) {
            $currency_code = trim($currency_code);
            $currency_code = strtoupper($currency_code);
            if (strlen($currency_code) == 3) {
                $this->BaseRequestParameters['currency'] = $rtlo;
            }
        }
    }

    /**
     * Set the sub-account layout code (rtlo).
     *
     * @internal
     *
     * @param integer $rtlo TargetPay sub-account layout code (rtlo).
     */
    final private function setRtlo($rtlo)
    {
        if (!is_numeric($rtlo)) {
            throw new \InvalidArgumentException('TP0001 No layout code', (int) base_convert('TP0001', 36, 10));
        } elseif (!is_int($rtlo)) {
            $rtlo = (int) $rtlo;
        }
        $this->BaseRequestParameters['rtlo'] = $rtlo;
    }
}
