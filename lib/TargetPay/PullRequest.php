<?php
namespace TargetPay;

/**
 * Transaction Validation Pull Request
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.0
 */
class PullRequest implements PullRequestInterface
{
    /** @type string $BaseRequest Base URL for a TargetPay validation request. */
    private $BaseRequest;

    /** @type integer $LayoutCode TargetPay sub-account layout code (rtlo). */
    private $LayoutCode;

    /**
     * @type boolean $Once Check a transaction only once (default true) or
     *     multiple times (false).
     */
    private $Once = true;

    /** @type string $Response Response or likely response from TargetPay. */
    private $Response;

    /**
     * @type boolean $Test Use in a production environment (default false) or
     *     run a test (true).
     */
    private $Test = false;

    /** @type string $TransactionID Transaction identifier (trxid). */
    private $TransactionID;


    /**
     * Pull Request Constructor
     *
     * @uses \TargetPay\PullRequestInterface
     *
     * @param string  $type  TargetPay pull request type.
     * @param integer $rtlo  TargetPay sub-account layout code (rtlo).
     * @param string  $trxid TargetPay transaction identifier (trxid).
     * @param boolean $once  Check only once (true) or not (false).
     * @param boolean $test  Run a test (true) or not (false).
     */
    public function __construct($type, $rtlo, $trxid = null, $once = true, $test = false)
    {
        $this->BaseRequest = rtrim($type, '?') . '?';

        $this->setRtlo($rtlo);

        if ($trxid !== null) {
            $this->setTransactionID($trxid);
        } else {
            if (isset($_GET['trxid']) && is_string($_GET['trxid'])) {
                $trxid = strip_tags($_GET['trxid']);
                $trxid = trim($trxid);
                if (is_numeric($trxid)) {
                    $this->setTransactionID($trxid);
                }
            }
        }

        $this->setOnce($once);
        $this->setTest($test);
    }

    /**
     * Get the pull request.
     *
     * @api
     *
     * @param void
     *
     * @return string TargetPay validation URL.
     *
     * @todo The TargetPay API responses are inconsistent.  For example, a bad
     *     request (Q) without a transaction ID (trxid) will result in a
     *     different error number and error message in the response (A):
     *     "TP0021 No transaction ID given" for Mister Cash and "TP0022 No
     *     transaction found with this ID." for iDEAL.
     *
     *     - Bancontact/Mister Cash:
     *       - Q: https://www.targetpay.com/mrcash/check?rtlo=<...>&once=0&test=1
     *       - A: TP0021 No transaction ID given
     *
     *     - iDEAL:
     *       - Q: https://www.targetpay.com/ideal/check?rtlo=<...>&once=1&test=1
     *       - A: TP0022 No transaction found with this ID.
     *
     *     - iDEAL:
     *       - Q: https://www.targetpay.com/ideal/check?once=1&test=1
     *       - A: TP0022 No transaction found with this ID.
     */
    public function getRequest()
    {
        $url = $this->BaseRequest . 'rtlo=' . $this->LayoutCode;
        if (isset($this->TransactionID)) {
            $url .= '&trxid=' . urlencode($this->TransactionID);
        }
        if ($this->Once) {
            $url .= '&once=1';
        } else {
            $url .= '&once=0';
        }
        if ($this->Test) {
            $url .= '&test=1';
        } else {
            $url .= '&test=0';
        }
        return $url;
    }

    /**
     * Get the TargetPay response.
     *
     * @api
     *
     * @param void
     *
     * @return string Returns the TargetPay response or the likely response
     *     if the response is known in advance.
     */
    public function getResponse()
    {
        return $this->Response;
    }

    /**
     * Alias of validate().
     */
    public function pull()
    {
        return $this->validate();
    }

    /**
     * Enable/disable multiple pulls.
     *
     * @api
     *
     * @param boolean $once Check only once (default true) or not (false).
     */
    public function setOnce($once = true)
    {
        if (!is_bool($once)) {
            if ($once == 0) {
                $this->Once = false;
            } else {
                $this->Once = true;
            }
        } else {
            $this->Once = $once;
        }
    }

    /**
     * Set the response.
     *
     * @internal
     *
     * @param string $response TargetPay API or cURL response.
     */
    private function setResponse($response)
    {
        if (is_string($response)) {
            $this->Response = trim($response);
        }
    }

    /**
     * Set the sub-account layout code (rtlo).
     *
     * @internal
     *
     * @param integer $rtlo TargetPay sub-account layout code (rtlo).
     */
    private function setRtlo($rtlo)
    {
        if (!is_numeric($rtlo)) {
            throw new \InvalidArgumentException('TP0001 No layout code', (int) base_convert('TP0001', 36, 10));
        } elseif (!is_int($rtlo)) {
            $rtlo = (int) $rtlo;
        }
        $this->LayoutCode = $rtlo;
    }

    /**
     * Set the transaction ID.
     *
     * @api
     *
     * @param string $trxid TargetPay transaction identifier (trxid).
     */
    public function setTransactionID($trxid)
    {
        $this->TransactionID = $trxid;
    }

    /**
     * Enable/disable testing.
     *
     * @api
     *
     * @param boolean $test Run a test (true) or not (default false).
     */
    public function setTest($test = false)
    {
        if (!is_bool($test)) {
            if ($test == 1) {
                $this->Test = true;
            } else {
                $this->Test = false;
            }
        } else {
            $this->Test = $test;
        }
    }

    /**
     * Start the payment transaction validation.
     *
     * @param void
     *
     * @return boolean This method validates a payment transaction silently: it
     *     will return true or false only.  Upon completion of the validation
     *     request, the response is available through the getResponse() method.
     */
    public function validate()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getRequest());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) {
            $curl_error = curl_error($ch);
            if (!empty($curl_error)) {
                $curl_errno = curl_errno($ch);
                if ($curl_errno != 0) {
                    $this->setResponse($curl_errno . ' ' . $curl_error);
                } else {
                    $this->setResponse($curl_error);
                }
            }
            return false;
        } else {
            $this->setResponse($response);
            return true;
        }
    }
}
