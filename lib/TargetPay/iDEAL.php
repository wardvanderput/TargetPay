<?php
namespace TargetPay;

/**
 * iDEAL
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.0.4
 *
 * The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT",
 * "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this
 * document are to be interpreted as described in RFC 2119.
 */
class iDEAL extends AbstractPayment
{
    /**
     * @type integer MINIMUM_AMOUNT Minimum amount in cents (EUR 0.84).
     * @type integer MAXIMUM_AMOUNT Maximum amount in cents (EUR 10,000.00).
     */
    const MINIMUM_AMOUNT = 84;
    const MAXIMUM_AMOUNT = 1000000;

    /**
     * @type array|boolean Array with iDEAL issuers or false if the issuers
     *     were not set yet.  Note that the issuers SHOULD be cached for
     *     security: the first request provides a list of selectable issuers
     *     and a selected issuer in the second request MUST therefore always
     *     be one of the previously provided issuers.
     */
    protected $Issuers = false;

    /** @type string $BaseRequest TargetPay URL to start an iDEAL transaction. */
    protected $BaseRequest = 'https://www.targetpay.com/ideal/start';

    /**
     * @type array $BaseRequestParameters Request parameters for the TargetPay
     *     iDEAL URI.  If 'cinfo_in_callback' is set to 1, customer information
     *     is included in the API response.  The customer info consists of the
     *     customer name and the International Bank Account Number (IBAN).
     */
    protected $BaseRequestParameters = array(
        'cinfo_in_callback' => 1
    );

    /**
     * Get the iDEAL issuers.
     *
     * @api
     *
     * @param void
     *
     * @return array Associative array with iDEAL issuing banks.
     */
    public function getIssuers()
    {
        if ($this->Issuers === false) {
            $this->loadIssuers();
        }
        return $this->Issuers;
    }

    /**
     * Last Known Good.
     *
     * @api
     *
     * @param void
     *
     * @return array This method returns a "last known good" array of known
     *     iDEAL issuing banks.  This array MAY be used if the initial issuer
     *     request fails for some reason.
     */
    public function getKnownIssuers()
    {
        $issuers = array(
            '0031' => 'ABN Amro',
            '0761' => 'ASN Bank',
            '0091' => 'Friesland Bank',
            '0721' => 'ING',
            '0801' => 'Knab',
            '0021' => 'Rabobank',
            '0771' => 'RegioBank',
            '0751' => 'SNS Bank',
            '0511' => 'Triodos Bank',
            '0161' => 'Van Lanschot Bankiers',
        );
        return $issuers;
    }

    /**
     * Validate the issuer ID.
     *
     * @api
     *
     * @param string $issuer_id Identifier of an iDEAL issuer.
     *
     * @return boolean Returns true if the issuer exists, otherwise false.
     */
    public function isIssuer($issuer_id)
    {
        if (!is_numeric($issuer_id)) {
            return false;
        }
        if (strlen($issuer_id) != 4) {
            return false;
        }

        $issuers = $this->getIssuers();
        if (!is_array($issuers)) {
            $issuers = $this->getKnownIssuers();
        }
        return array_key_exists($issuer_id, $issuers);
    }

    /**
     * Load the TargetPay iDEAL issuers.
     *
     * TargetPay support four requests for iDEAL issuers: one for an HTML form,
     * one for XML data, and two for JavaScript in both Dutch (nl) and
     * English (en).  The four request URI's are listed below.
     *
     * @link https://www.targetpay.com/ideal/getissuers.php
     * @link https://www.targetpay.com/ideal/getissuers.php?format=xml
     * @link https://www.targetpay.com/ideal/issuers-nl.js
     * @link https://www.targetpay.com/ideal/issuers-en.js
     *
     * @internal
     *
     * @param void
     *
     * @return void
     *
     * @throws \RuntimeException A Standard PHP Library (SPL) runtime exception
     *     is thrown if the cURL request fails.
     */
    private function loadIssuers()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.targetpay.com/ideal/getissuers.php?format=xml');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $xml = curl_exec($ch); 
        if ($xml === false) {
            throw new \RuntimeException(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        unset($ch);

        $dom = new \DomDocument();
        $dom->loadXml($xml);
        $issuers = array();
        foreach ($dom->getElementsByTagName('issuer') as $issuer) {
            $issuers[$issuer->getAttribute('id')] = $issuer->nodeValue;
        }

        $this->Issuers = $issuers;
    }

    /**
     * Display/hide customer information.
     *
     * @param boolean $cinfo_in_callback Display customer information (default
     *     true) in the API callback or hide it (false).
     *
     * @return $this
     */
    public function setCustomerInformation($cinfo_in_callback = true)
    {
        if (!is_bool($cinfo_in_callback)) {
            if ($cinfo_in_callback == 1) {
                $cinfo_in_callback = true;
            } else {
                $cinfo_in_callback = false;
            }
        }

        if ($cinfo_in_callback) {
            $this->BaseRequestParameters['cinfo_in_callback'] = 1;
        } else {
            unset($this->BaseRequestParameters['cinfo_in_callback']);
        }
        return $this;
    }

    /**
     * Set the iDEAL issuer.
     *
     * @api
     *
     * @param string $issuer_id Identifier of an iDEAL issuer, usually a Dutch
     *     bank.  The issuer ID is validated, so there is no need for a prior
     *     call to the isIssuer() method.
     *
     * @return $this
     *
     * @throws \UnexpectedValueException An SPL unexpected value runtime
     *     exception is thrown if the issuer ID is invalid.  Note that this
     *     event SHOULD be logged as this MAY be caused by a client request
     *     that has been tempered with.
     */
    public function setIssuer($issuer_id)
    {
        if ($this->isIssuer($issuer_id)) {
            $this->BaseRequestParameters['bank'] = $issuer_id;
        } else {
            throw new \UnexpectedValueException(
                'TP0005 No bank ID.',
                (int) base_convert('TP0005', 36, 10)
            );
        }
        return $this;
    }
}
