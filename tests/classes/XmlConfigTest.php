<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2011, AKSW
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * A copy of the GNU General Public License is bundled with this package in
 * the file LICENSE.txt. It is also available through the world-wide-web at 
 * this URL: http://opensource.org/licenses/gpl-2.0.php
 *
 * @category   OntoWiki Formgenerator
 * @package    Classes
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
 
/*
 * Helper file, that adjusts the include_path and initializes the test environment.
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'ExtensionTestHelper.php';


// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'XmlConfigTest::main');
}

require_once EXTENSIONS_PATH . 'formgenerator/classes/XmlConfig.php';
require_once EXTENSIONS_PATH . 'formgenerator/classes/Formula.php';
require_once EXTENSIONS_PATH . 'formgenerator/helper.php';

/**
 * This test class comtains tests for the Classes of the Ontiwiki extension Formgenerator.
 * 
 * @category   OntoWiki Formgenerator
 * @package    Classes
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Lars Eidam <lars.eidam@studserv.uni-leipzig.de>
 */
class XmlConfigTest extends PHPUnit_Framework_TestCase
{
    protected $_xmlconfig;
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('XmlConfigTest'));
    }
    
    public function setUp()
    {
        $this->_xmlconfig = new XMLConfig();
    }
    
    public function tearDown()
    {
        unset($this->_xmlconfig);
    }
    
    public function testloadFile()
    {
        $this->_xmlconfig->loadFile(dirname ( __FILE__ ) . '/testform.xml');
        
    }
    public function testreplaceNamespace()
    {
        $newstr = $this->_xmlconfig->replaceNamespace('architecture:firstname');
        $this->assertSame(
            'http://als.dispedia.info/architecture/c/20110504/' . 'firstname',
            $newstr,
            "Replacing 'architecture:' are not correct."
        );
    }
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'XmlConfigTest::main') {
    XmlConfigTest::main();
}
