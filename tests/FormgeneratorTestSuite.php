<?php

require_once _TESTROOT . 'TestHelper.php';

require_once 'classes/ClassesTestSuite.php';

class Formgenerator_TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * The main method, which executes all controller tests.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    /**
     * Returns a test suite containing all controller tests.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Formgenerator Tests');
        
        $suite->addTestSuite('Classes_TestSuite');
        
        return $suite;
    }
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'Formgenerator_TestSuite::main') {
    Formgenerator_TestSuite::main();
}
