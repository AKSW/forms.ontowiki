<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2010, AKSW
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
 * @category   OntoWiki
 * @package    Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 */
 
/*
 * Helper file, that adjusts the include_path and initializes the test environment.
 */
require_once _TESTROOT . 'TestHelper.php';


// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'ResourceTest::main');
}

require_once EXTENSIONS_PATH . 'formgenerator/classes/Resource.php';

/**
 * This test class comtains tests for the OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    Version
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class ResourceTest extends PHPUnit_Framework_TestCase
{
    protected $_resource;
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('ResourceTest'));
    }
    
    public function setUp()
    {
        $this->_resource = new Resource ();
    }
    
    public function testGenerateUniqueUri()
    {
        $newUri = $this->_resource->generateUniqueUri( 'http://testserver.de/', 'TestClass', 'TestLabel', "%modeluri%/i/%classname%/%date%/%hash%/%labelparts%");
        
        echo $newUri;
        $this->assertEquals("Test Description", $newUri);
    }
    
       
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'ResourceTest::main') {
    ResourceTest::main();
}
