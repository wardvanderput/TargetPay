<?php
/**
 * iDEAL voorbeeld 1
 *
 * Dit eerste voorbeeld illustreert de eerste stap bij het starten van een
 * iDEAL-betaling: het tonen van een keuzelijst met issuers die iDEAL
 * aanbieden.  Doorgaans zijn deze issuers Nederlandse banken.
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
    </style>
  </head>
  <body>
    <!-- Voor tests staat de HTTP-methode op GET -->
    <form action="" method="get">
      <div>
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
