#!/usr/bin/env php
<?php
/**
 * Build a PHPCS phar.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Benjamin Pearson <bpearson@squiz.com.au>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

error_reporting(E_ALL | E_STRICT);

if (ini_get('phar.readonly') === '1') {
    echo 'Unable to build, phar.readonly in php.ini is set to read only.'.PHP_EOL;
    exit(1);
}

$cwd = getCwd();
require_once __DIR__.'/../CodeSniffer.php';

$script = 'phpcs';

echo "Building $script phar".PHP_EOL;

$pharFile = $cwd.'/'.$script.'.phar';
echo "\t=> $pharFile".PHP_EOL;
if (file_exists($pharFile) === true) {
    echo "\t** file exists, removing **".PHP_EOL;
    unlink($pharFile);
}

$phar = new Phar($pharFile, 0, $script.'.phar');

echo "\t=> adding files from package list... ";
buildFromPackage($phar);
echo 'done'.PHP_EOL;

echo "\t=> adding stub... ";
$stub  = '#!/usr/bin/env php'."\n";
$stub .= '<?php'."\n";
$stub .= 'Phar::mapPhar(\''.$script.'.phar\');'."\n";
$stub .= 'require_once "phar://'.$script.'.phar/CodeSniffer/CLI.php";'."\n";
$stub .= '$cli = new PHP_CodeSniffer_CLI();'."\n";
$stub .= '$cli->run'.$script.'();'."\n";
$stub .= '__HALT_COMPILER();';
$phar->setStub($stub);
echo 'done'.PHP_EOL;

/**
 * Build from a package list.
 *
 * @param object &$phar The Phar class.
 *
 * @return void
 */
function buildFromPackage(&$phar)
{
    $package_list = include dirname(__FILE__).'/../package.php';

    foreach ($package_list as $item) {
        $packages[$item] = dirname(__FILE__).'/../'.$item;
    }

    $phar->buildFromIterator(new ArrayIterator($packages));

    foreach ($package_list as $package) {
        $phar[$package]->compress(Phar::GZ);
    }

}//end buildFromPackage()