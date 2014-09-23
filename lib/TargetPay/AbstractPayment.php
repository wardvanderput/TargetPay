<?php
namespace TargetPay;

/**
 * Abstract TargetPay Payments Class
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.5.0
 *
 * The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT",
 * "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this
 * document are to be interpreted as described in RFC 2119.
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

    /** @type string RedirectURI URI for client redirection. */
    protected $RedirectURI;

    /**
     * @type string|boolean Response String containing the TargetPay API
     *     response or false if there is no response.
     */
    protected $Response = false;

    /** @type string TransactionID TargetPay transaction identifier (trxid) */
    protected $TransactionID;

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
     * Get the description.
     *
     * @param void
     *
     * @return string|null Transaction description.
     */
    public function getDescription()
    {
        if (isset($this->BaseRequestParameters['description'])) {
            return $this->BaseRequestParameters['description'];
        } else {
            return null;
        }
    }

    /**
     * Get the redirection URI.
     *
     * @param void
     *
     * @return string Returns the URI to redirect the client.
     */
    public function getRedirectURI()
    {
        return $this->RedirectURI;
    }

    /**
     * Alias of getRedirectURI()
     */
    public function getRedirectURL()
    {
        return $this->getRedirectURI();
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
     * Get the response.
     *
     * @param void
     *
     * @return string Returns the full string containing the TargetPay API
     *     response.  If there is no response yet, this method will try to get
     *     a response from the API by starting a transaction.
     */
    public function getResponse()
    {
        if ($this->Response !== false) {
            return $this->Response;
        } else {
            $this->startTransaction();
            return $this->Response;
        }
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
     * Get the transaction ID.
     *
     * @api
     *
     * @param void
     *
     * @return string Returns the TargetPay transaction identifier (trxid).
     */
    public function getTransactionID()
    {
        return $this->TransactionID;
    }

    /**
     * Get the user IP address.
     *
     * @param void
     *
     * @return string|null Remote client IP address.
     */
    public function getUserIP()
    {
        if (isset($this->BaseRequestParameters['userip'])) {
            return $this->BaseRequestParameters['userip'];
        } else {
            return null;
        }
    }

    /**
     * Set the amount.
     *
     * @api
     *
     * @param integer $amount Transaction amount in cents.
     *
     * @return $this
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
            throw new \InvalidArgumentException(
                'setAmount() expects parameter 1 to be an integer for an amount in cents, '
                . gettype($amount) . ' given'
            );
        } else {
            $amount = (int) $amount;
        }
        if ($amount < static::MINIMUM_AMOUNT) {
            throw new \DomainException(
                'TP0002 Amount too low.',
                (int) base_convert('TP0002', 36, 10)
            );
        } elseif ($amount > static::MAXIMUM_AMOUNT) {
            throw new \DomainException(
                'TP0003 Amount too high.',
                (int) base_convert('TP0003', 36, 10)
            );
        } else {
            $this->BaseRequestParameters['amount'] = $amount;
        }
        return $this;
    }

    /**
     * Set the currency.
     *
     * @api
     *
     * @param string $currency_code ISO 4217 currency code.  Defaults to 'EUR'
     *     for the European euro.
     *
     * @return $this
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
        return $this;
    }

    /**
     * Set the transaction description.
     *
     * @api
     *
     * @param string $description Short human-readable description of the
     *     payment transaction.  This description generally SHOULD NOT exceed
     *     a 32 characters limit, but a longer string will be truncated
     *     silently.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException Throws an SPL invalid argument
     *     exception if the description is not a string or an empty string.
     *
     * @throws \DomainException Throws TargetPay error TP0006 as an SPL domain
     *     exception if the description is empty.
     */
    public function setDescription($description)
    {
        if (is_int($description)) {
            $description = (string) $description;
        } elseif (!is_string($description)) {
            throw new \InvalidArgumentException(
                'setDescription() expects parameter 1 to be a string, '
                . gettype($description) . ' given'
            );
        } else {
            $description = str_replace('€ ', 'EUR ', $description);
            $description = str_replace(' €', ' euro', $description);
            $description = str_replace('€', ' EUR ', $description);
            $description = preg_replace('!\s+!', ' ', $description);
            $description = trim($description, "\x00..\x20");
            if (strlen($description) > 32) {
                $description = substr($description, 0, 32);
            }
        }

        if (empty($description)) {
            throw new \DomainException(
                'TP0006 No description.',
                (int) base_convert('TP0006', 36, 10)
            );
        } else {
            $this->BaseRequestParameters['description'] = $description;
        }

        return $this;
    }

    /**
     * Set the user interface language.
     *
     * @api
     *
     * @param string $language_id ISO 639 language identifier.  Defaults to
     *     'nl' for Dutch, because TargetPay, as a payment service provider
     *     from the Netherlands, is most likely used to handle online payments
     *     by Dutch-speaking customers.
     *
     * @return $this
     */
    public function setLanguage($language_code = 'nl')
    {
        if (is_string($language_code)) {
            $language_code = trim($language_code);
            $language_code = strtolower($language_code);
            if (strlen($language_code) == 2) {
                $this->BaseRequestParameters['lang'] = $language_code;
            }
        }
        return $this;
    }

    /**
     * Set the report URL.
     *
     * @api
     *
     * @param $report_url Private URL or URI for push messaging from TargetPay.
     *     The report URL is optional, but MUST be kept hidden from any third
     *     parties.  If the report URL is set to the return URL, it will
     *     therefore be ignored.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException Throws an SPL invalid argument
     *     exception if the report URL is invalid.  Note that the TargetPay API
     *     DOES NOT validate a report URL.
     */
    public function setReportURL($report_url)
    {
        if (!filter_var($report_url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid report URL');
        }
        if ($report_url !== $this->BaseRequestParameters['returnurl']) {
            $this->BaseRequestParameters['reporturl'] = $report_url;
        }
        return $this;
    }

    /**
     * Set the return URL.
     *
     * @api
     *
     * @param $return_url Public URL or URI to redirect the client to upon
     *     completion or cancellation of the payment.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException Throws TargetPay error TP0004 as an
     *     SPL invalid argument exception if the URL is invalid.  Note that the
     *     function filter_var() will only find ASCII URLs to be valid;
     *     internationalized domain names (containing non-ASCII characters)
     *     will fail.
     */
    public function setReturnURL($return_url)
    {
        if (!filter_var($return_url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(
                'TP0004 No or invalid return URL.',
                (int) base_convert('TP0004', 36, 10)
            );
        }
        $this->BaseRequestParameters['returnurl'] = $return_url;
        return $this;
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
            // The actual API response does not include a space:
            // TP0001 No layoutcode.
            throw new \InvalidArgumentException(
                'TP0001 No layout code.',
                (int) base_convert('TP0001', 36, 10)
            );
        } elseif (!is_int($rtlo)) {
            $rtlo = (int) $rtlo;
        }
        $this->BaseRequestParameters['rtlo'] = $rtlo;
    }

    /**
     * Set the user IP address.
     *
     * @api
     *
     * @param string $userip Optional Internet Protocol (IP) client address.
     *     If the user IP address is omitted, it is set to the default remote
     *     address set by the server.
     *
     * @return $this
     *
     * @throws \UnexpectedValueException Throws TargetPay API error TP0009 as
     *     an SPL unexpected value runtime exception on an invalid or missing
     *     user IP address.
     */
    public function setUserIP($userip = null)
    {
        if ($userip == null) {
            $userip = $_SERVER['REMOTE_ADDR'];
        }

        $userip = filter_var($userip, FILTER_VALIDATE_IP);
        if ($userip === false) {
            throw new \UnexpectedValueException(
                'TP0009 Invalid or no user IP given.',
                (int) base_convert('TP0009', 36, 10)
            );
        } else {
            $this->BaseRequestParameters['userip'] = $userip;
        }

        return $this;
    }

    /**
     * Start the payment transaction.
     *
     * @api
     *
     * @param void
     *
     * @return boolean Returns true if the transaction was started successfully
     *     or false if an error occurred.  If a successful transaction has
     *     already been started, it will not be restarted.
     */
    public function startTransaction()
    {
        if (isset($this->TransactionID)) {
            return true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getRequest());
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $response = curl_exec($ch); 
        if ($response === false) {
            $this->Response = curl_error($ch);
            return false;
        } else {
            $this->Response = $response;
        }
        curl_close($ch);
        unset($ch);

        $response = explode(' ', $response);
        if (count($response) !== 2) {
            return false;
        }

        if ($response[0] !== '000000') {
            return false;
        } else {
            $transaction = explode('|', $response[1]);
            $this->TransactionID = $transaction[0];
            $this->RedirectURI   = $transaction[1];
            return true;
        }
    }
}
