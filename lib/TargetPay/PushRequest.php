<?php
namespace TargetPay;

/**
 * Transaction Validation Push Request
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   0.1.3
 *
 * The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT",
 * "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this
 * document are to be interpreted as described in RFC 2119.
 */
class PushRequest
{
    /** @param array $Data Generic data container. */
    private $Data = array();

    /**
     * Request Contructor
     */
    public function __construct()
    {
        // Initialize a response with common HTTP headers
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        // Allow POST requests only
        if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            if ($this->isTrustedClient()) {
                header('HTTP/1.1 405 Method Not Allowed');
                header('Allow: POST');
            } else {
                $this->fail();
            }
        } elseif ($this->isTrustedClient() !== true) {
            $this->fail();
        } else {
            $this->populate();

            // Respond with a plain-text OK
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'OK';
        }
    }

    /**
     * Magic Getter
     *
     * @param string $key Case-insensitive parameter name.
     *
     * @return mixed Request parameter.
     */
    public function __get($key)
    {
        $key = strtolower($key);

        if ($key == 'status') {
            return $this->getStatus();
        }

        if ($key == 'trxid') {
            return $this->getTransactionID();
        }

        if (array_key_exists($key, $this->Data)) {
            return $this->Data[$key];
        } else {
            return null;
        }
    }

    /**
     * Let the request fail silently.
     *
     * The report URL SHOULD BE used by the TargetPay API only.  It MUST NOT
     * ever be exposed to any third parties.  In the unlikely event the URL is
     * called by a third party, it is hidden by a generic "404 Not Found" HTTP
     * error.
     *
     * @param void
     */
    public function fail()
    {
        if (strtoupper($_SERVER['SERVER_PROTOCOL']) == 'HTTP1/0') {
            header('HTTP/1.0 404 Not Found');
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    /**
     * Get the transaction status.
     *
     * @api
     *
     * @param void
     *
     * @return string|null Returns the reported TargetPay transaction status or
     *     null if the transaction status is unknown.
     */
    public function getStatus()
    {
        if (array_key_exists('status', $this->Data)) {
            return $this->Data['status'];
        } else {
            return null;
        }
    }

    /**
     * Get the transaction ID.
     *
     * @api
     *
     * @param void
     *
     * @return string|null Returns the TargetPay transaction identifier (trxid)
     *     or null if there is no transaction ID.
     */
    public function getTransactionID()
    {
        if (array_key_exists('trxid', $this->Data)) {
            return $this->Data['trxid'];
        } else {
            return null;
        }
    }

    /**
     * Did this transaction succeed?
     *
     * @param void
     *
     * @return boolean Returns true if the current transaction status is set to
     *     "Success", otherwise false.
     */
    public function isSuccess()
    {
        $status = $this->getStatus();
        if (!is_string($status)) {
            return false;
        }

        if (strtolower($status) == 'success') {
            return true;
        } elseif (substr($status, 0, 9) == '000000 OK') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check for TargetPay clients.
     *
     * @param void
     *
     * @return boolean Returns true if the upstream client is located in one of
     *     the TargetPay network IPv4 ranges, otherwise false.  This method
     *     supports the new IP range starting with 78.152.58, which will be in
     *     operation as of September 29, 2014.
     */
    public function isTrustedClient()
    {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return false;
        }

        if (
            substr($_SERVER['REMOTE_ADDR'], 0, 10) == '89.184.168'
            || substr($_SERVER['REMOTE_ADDR'], 0, 9) == '78.152.58'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Populate the data container.
     *
     * @internal
     *
     * @param void
     */
    private function populate()
    {
        $data = array();

        if (count($_GET) > 0) {
            foreach ($_GET as $key => $value) {
                if (is_string($value) && !empty($value)) {
                    $key = strtolower(urldecode($key));
                    $data[$key] = $value;
                }
            }
        }

        if (count($_POST) > 0) {
            foreach ($_POST as $key => $value) {
                if (is_string($value) && !empty($value)) {
                    $key = strtolower($key);
                    $data[$key] = $value;
                }
            }
        }

        $this->Data = $data;
    }
}
