<?php

/*
 * @package test
 */
 

error_reporting(E_ALL);
require_once('/Users/alanpinstein/dev/sandbox/phocoadev/phocoadev/conf/webapp.conf');
require_once('framework/WFWebApplication.php');
require_once('framework/WFObject.php');
require_once('framework/WFError.php');

require_once "TestObjects.php";

class KeyValueCodingTest extends PHPUnit_Framework_TestCase
{
    protected $parent;
    protected $child;
    protected $nodeTree;
    protected $objectHolder;

    function setUp()
    {
        $this->parent = new Person('John', 'Doe', 1);
        $this->child = new Person('John', 'Doe, Jr.', 2);
        
        // set up complex tree
        /**
         * Grandaddy            85
         *    Daddy             50
         *       Grandkid1      22
         *       Grandkid2      25
         *    Aunt              48
         *       Grandkid3      18
         */
        $granddaddy = new Node;
        $granddaddy->name = 'Granddaddy';
        $granddaddy->value = 85;

        $bro1 = new Node;
        $bro1->name = 'Daddy';
        $bro1->value = 50;
        $granddaddy->addChild($bro1);

        $sis1 = new Node;
        $sis1->name = 'Aunt';
        $sis1->value = 48;
        $granddaddy->addChild($sis1);

        $grandkid1 = new Node;
        $grandkid1->name = 'Grandkid1';
        $grandkid1->value = 22;
        $bro1->addChild($grandkid1);
        
        $grandkid2 = new Node;
        $grandkid2->name = 'Grandkid2';
        $grandkid2->value = 25;
        $bro1->addChild($grandkid2);

        $grandkid3 = new Node;
        $grandkid3->name = 'Grandkid3';
        $grandkid3->value = 18;
        $sis1->addChild($grandkid3);

        $this->nodeTree = $granddaddy;

        $objectHolder = new ObjectHolder;
        $objectHolder->myObject = $granddaddy;
        $this->objectHolder = $objectHolder;
    }


    // test @distinctUnionOfObjects
    function testDistinctUnionOfObjectsOperator()
    {
        $distinctUnionOfObjects = $this->nodeTree->valueForKeyPath('childrenDuplicated.@distinctUnionOfObjects.firstChild');
        self::assertTrue(count($distinctUnionOfObjects) == 2 and $distinctUnionOfObjects[0]->name == 'Grandkid1' and $distinctUnionOfObjects[1]->name == 'Grandkid3');
    }

    // test @distinctUnionOfArrays
    function testDistinctUnionOfArraysOperator()
    {
        $distinctUnionOfArrays = $this->nodeTree->valueForKeyPath('children.@distinctUnionOfArrays.childrenDuplicated');
        $names = array();
        foreach ($distinctUnionOfArrays as $node) {
            $names[] = $node->name;
        }
        sort($names);
        self::assertTrue(count($distinctUnionOfArrays) == 3 and $names[0] == 'Grandkid1' and $names[1] == 'Grandkid2' and $names[2] == 'Grandkid3');
    }

    // test @unionOfArrays
    function testUnionOfArraysOperator()
    {
        $unionOfArrays = $this->nodeTree->valueForKeyPath('children.@unionOfArrays.children');
        self::assertTrue(count($unionOfArrays) == 3);
    }

    // test @sum
    function testSumOperator()
    {
        $sum = $this->nodeTree->valueForKeyPath('children.@sum.value');
        self::assertTrue($sum == 98);
    }

    // test @min
    function testMinOperator()
    {
        $min = $this->nodeTree->valueForKeyPath('children.@min.value');
        self::assertTrue($min == 48);
    }

    // test @max
    function testMaxOperator()
    {
        $max = $this->nodeTree->valueForKeyPath('children.@max.value');
        self::assertTrue($max == 50);
    }

    // test @avg
    function testAverageOperator()
    {
        $avg = $this->nodeTree->valueForKeyPath('children.@avg.value');
        self::assertTrue($avg == 49);
    }

    // test @count
    function testCountOperator()
    {
        $g1Count = $this->nodeTree->valueForKeyPath('children.@count');
        $g1Count2 = $this->objectHolder->valueForKeyPath('myObject.children.@count');
        self::assertTrue($g1Count == $g1Count2 and $g1Count == 2);
    }

    // test getting an array back from a keypath
    function testValueForKeyPathArrayFromKeyPath()
    {
        $result = $this->objectHolder->valueForKeyPath('myObject.children');
        self::assertTrue($result[0]->name == 'Daddy' and $result[1]->name == 'Aunt' and count($result) == 2);
    }

    // test getting an array back from first key
    function testValueForKeyPathArray()
    {
        $result = $this->nodeTree->valueForKeyPath('children');
        self::assertTrue($result[0]->name == 'Daddy' and $result[1]->name == 'Aunt' and count($result) == 2);
    }

    // test getting magic data back with no operator
    function testValueForKeyPathMagicArray()
    {
        $result = $this->nodeTree->valueForKeyPath('children.name');
        self::assertTrue($result[0] == 'Daddy' and $result[1] == 'Aunt' and count($result) == 2);
    }

    function testSetValueForKey()
    {
        $this->parent->setValueForKey('My Last Name', 'lastName');
        self::assertTrue($this->parent->lastName == 'My Last Name');

        $this->parent->setValueForKey($this->child, 'child');
        self::assertTrue($this->parent->child->uid == 2);
    }

    function testKeyValueValidationGeneratesErrorForBadValue()
    {
        $badvalue = 'badfirstname';
        $edited = false;
        $errors = array();
        $valid = $this->parent->validateValueForKey($badvalue, 'firstName', $edited, $errors);
        self::assertTrue($valid === false and count($errors) == 1);
    }
}

?>
