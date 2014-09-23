<?php
/**
 * Handling TargetPay Push Requests
 *
 * If the OPTIONAL report URL is set when starting an online payment
 * transaction, the TargetPay API will post push notifications back to the
 * report URL.  These status updates MAY be used to update or log the current
 * state of the transaction.
 *
 * If the transaction was completed successfully, the isSuccess() method will
 * return true.  The current status message is available through the
 * getStatus() method.  If the transaction failed, the error message is also
 * returned by the getStatus() method, so logging this API response is
 * RECOMMENDED.
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   1.0.0
 *
 * The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT",
 * "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this
 * document are to be interpreted as described in RFC 2119.
 */

// Load the TargetPay push request class
require '../lib/TargetPay/PushRequest.php';
// Instantiate the request handler
$message = new \TargetPay\PushRequest();

// Get the TargetPay transaction ID (trxid)
$trxid = $message->getTransactionID();

// Check if the payment transaction succeeded
if ($message->isSuccess()) {
    // Update the completed payment in a shopping cart or session
    // <...>
} else {
    // Log the status of a failed or uncompleted transaction
    $status = $message->getStatus();
    // <...>
}
