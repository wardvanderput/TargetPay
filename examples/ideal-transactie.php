<?php
/**
 * iDEAL voorbeeld 2
 *
 * Dit tweede voorbeeld illustreert het starten van een iDEAL-transactie.
 * Net zoals in het eerste voorbeeldbestand ideal-banken.php wordt eerst een
 * webformulier getoond voor het selecteren van een bank.  Vervolgens wordt
 * via de door de gebruiker geselecteerde bank een iDEAL-transactie gestart.
 *
 * Met dit werkende voorbeeld kun je online iDEAL-betalingen accepteren.  De
 * persoonlijke "rtlo" die je nodig hebt voor de TargetPay API, kun je bij
 * TargetPay aanvragen via:
 *
 * @link      https://www.targetpay.com/quickreg/69391
 *
 * @author    Ward van der Put <Ward.van.der.Put@gmail.com>
 * @copyright Copyright © 2014 E.W. van der Put
 * @license   http://www.gnu.org/licenses/gpl.html GPLv3
 * @version   1.0.0
 */

// Voor tests alle eventuele PHP-fouten rapporteren en weergeven
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');
 
// Alle TargetPay-interfaces en -klassen laden.
// Wijzig indien nodig het pad naar de library.
require '../lib/TargetPay/ClassLoader.php';

// Lay-outcode (rtlo) van een TargetPay-subaccount instellen
$rtlo = 69391;

// iDEAL-object instantiëren met de rtlo
$ideal = new \TargetPay\iDEAL($rtlo);

// Controleren of er een bank-ID is gepost
if (strtoupper(trim($_SERVER['REQUEST_METHOD'])) == 'POST') {
    if ($_POST['bank']) {
        if ($ideal->isIssuer($_POST['bank'])) {

            // Geselecteerde bank-ID doorgeven
            $ideal->setIssuer($_POST['bank']);

            // Bedrag van 1,25 euro (125 cent) instellen
            $ideal->setAmount(125);

            // Omschrijving van de betaling toevoegen
            $ideal->setDescription('Testbetaling met iDEAL');

            // Return-URL instellen.  De gebruiker wordt na het afronden of het
            // annuleren van de iDEAL-betaling doorverwezen naar deze URL.
            // Wijzig deze URL in een bestaande URL.
            $ideal->setReturnURL('http://www.example.com/foo/bar.php');

            // Transactie starten
            if ($ideal->startTransaction()) {
                // Bij succes de client omleiden naar de gekozen bank
                header('Location: ' . $ideal->getRedirectURI());
                exit;
            } else {
                $error = 'Er is een fout opgetreden: ' . htmlspecialchars($ideal->getResponse());
            }
        }
    }
}

// Array met alle banken opvragen
$banken = $ideal->getIssuers();

// Webpagina in HTML5 met formulier tonen
header('Content-Language: nl-NL');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>iDEAL</title>
    <style>
      body,
      html {
        background-color: #fff;
        color: #000;
        font-family: Arial, sans-serif;
        font-size: 10pt;
      }

      div {
        text-align: center;
      }

      input[type="submit"] {
        font-size: 10pt;
        min-width: 95px;
        padding: 1ex 1em;
      }

      select {
        font-size: 10pt;
        text-align: left;
        padding: 2px;
      }

      .error {
        background-color: #c00;
        color: #fff;
        margin: 1ex;
        padding: 1ex;
      }
    </style>
  </head>
  <body>
    <form action="" method="post">
      <div>
        <?php
        // Eventuele fout melden
        if (isset($error)) {
            echo '<p class="error">' . $error . '</p>';
        }
        ?>
        <select name="bank" required>
          <option value="">(selecteer een bank)</option>
          <?php
          // Alle banken tonen als opties in de keuzelijst
          foreach ($banken as $id => $naam) {
              echo '<option value="' . $id . '">' . $naam . '</option>';
          }
          ?>
        </select>
      </div>
      <div>
        <input type="submit" value="OK">
      </div>
    </form>
  </body>
</html>
