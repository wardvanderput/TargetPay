<?php
/**
 * TargetPay Class Loader
 *
 * This class loader MAY be used to load all PHP interfaces and PHP class 
 * files for the TargetPay class library.  The library does however 
 * offer full support of namespaces and auto loading, so using the class 
 * loader is optional.
 *
 * @version 0.0.1
 */

// Abstract payment class
require 'AbstractPayment.php';

// Payment methods extending the abstract payment class
require 'iDEAL.php';
require 'MisterCash.php';
require 'Paysafecard.php';
require 'SOFORTBanking.php';

// Payment transaction validation
require 'PullRequestInterface.php';
require 'PullRequest.php';
