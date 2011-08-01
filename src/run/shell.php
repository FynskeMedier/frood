<?php
/**
 * Run The Frood from a shell.
 *
 * Usage: php shell.php [module] [controller] [action] parameters...
 * Where parameters are on the form name=value
 *
 * Example: php shell.php plaza post new title=lol body="Omg teh lolz"
 *
 * PHP version 5
 *
 * @category   Module
 * @package    Frood
 * @subpackage Runners
 * @author     Jens Riisom Schultz <jers@fynskemedier.dk>
 * @since      2011-07-07
 */

require_once dirname(__FILE__) . '/../Frood.php';

if ($argc < 4) {
	throw new Exception('Usage: php shell.php [module] [controller] [action] parameters...');
}

$module     = $argv[1];
$controller = $argv[2];
$action     = $argv[3];

$args = array();
foreach (array_slice($argv, 4) as $arg) {
	$matches = array();

	switch (true) {
		case preg_match('/([^=]+)=(.+)/', $arg, $matches):
			if (substr($matches[2], 0, 7) == '_FILE_:') {
				$args[$matches[1]] = new FroodFileParameter(substr($matches[2], 7));
			} else {
				$args[$matches[1]] = $matches[2];
			}
			break;
		default:
			throw new Exception("Bogus parameter, $arg.");
	}
}

$frood = new Frood($module);

$parameters = new FroodParameters($args);

try {
	$frood->dispatch($controller, $action, $parameters);
} catch (Exception $e) {
	echo "Exception: {$e->getMessage()}\n";
}
