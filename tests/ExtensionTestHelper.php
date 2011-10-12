<?php

/**
 * OntoWiki test base file
 *
 * Sets the same include paths as OntoWiki uses and must be included
 * by all tests.
 *
 * @author     Lars Eidam <lars.eidam@studserv.uni-leipzig.de>
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

?>

<?php

//define('EXTENSIONS_PATH', realpath(dirname(__FILE__) . '/../..') . '/');
require_once dirname(dirname(dirname(dirname(__FILE__))))
             . DIRECTORY_SEPARATOR . 'application'
             . DIRECTORY_SEPARATOR . 'tests'
             . DIRECTORY_SEPARATOR . 'TestHelper.php';
