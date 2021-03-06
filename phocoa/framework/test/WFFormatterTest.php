<?php

/*
 * @package test
 */
 

error_reporting(E_ALL);
require_once('framework/WFWebApplication.php');
require_once('framework/WFObject.php');
require_once('framework/WFError.php');

class WFFormatterTest extends PHPUnit_Framework_TestCase
{
    protected $unixFormatter = NULL;
    protected $dateTimeFormatter = NULL;
    protected $sqlFormatter = NULL;

    protected $formatString = 'Y-m-d H:i:s A';  // 2006-01-01 11:59:59 AM
    protected $formatStringOutput = '2006-01-01 11:59:59 AM';
    protected $relFormatString = 'Y-m-d';       // 2006-01-01
    protected $relFormatStringOutput = '2006-01-01';

    protected $timestamp;
    protected $sqlTimestamp;

    protected $inputTime = '2006-01-01 11:59:59';

    function setUp()
    {
        $this->timestamp = strtotime($this->inputTime);
        $this->sqlTimestamp = $this->inputTime . '.415755-04';

        $this->unixFormatter = new WFUNIXDateFormatter();
        $this->unixFormatter->setFormatString($this->formatString);
        $this->unixFormatter->setRelativeDateFormatString($this->relFormatString);

        $this->dateTimeFormatter = new WFDateTimeFormatter();
        $this->dateTimeFormatter->setFormatString($this->formatString);
        $this->dateTimeFormatter->setRelativeDateFormatString($this->relFormatString);

        $this->sqlFormatter = new WFSQLDateFormatter();
        $this->sqlFormatter->setFormatString($this->formatString);
        $this->sqlFormatter->setRelativeDateFormatString($this->relFormatString);
    }

    function testSQLFormatter()
    {
        self::assertEquals($this->formatStringOutput, $this->sqlFormatter->stringForValue($this->sqlTimestamp));

        $this->sqlFormatter->setFormatString('+++');
        self::assertEquals($this->relFormatStringOutput, $this->sqlFormatter->stringForValue($this->sqlTimestamp));
    }

    function testUNIXFormatter()
    {
        self::assertEquals($this->formatStringOutput, $this->unixFormatter->stringForValue($this->timestamp));

        $this->unixFormatter->setFormatString('+++');
        self::assertEquals($this->relFormatStringOutput, $this->unixFormatter->stringForValue($this->timestamp));
    }

    function testRelDate()
    {
        $this->unixFormatter->setFormatString('+++');
        self::assertEquals('Today', $this->unixFormatter->stringForValue(time()));
        self::assertEquals('Tomorrow', $this->unixFormatter->stringForValue(time()+86400));
        self::assertEquals('in 2 days', $this->unixFormatter->stringForValue(time()+2*86400));
        self::assertEquals('Yesterday', $this->unixFormatter->stringForValue(time()-86400));
        self::assertEquals('2 days ago', $this->unixFormatter->stringForValue(time()-2*86400));
        self::assertEquals($this->relFormatStringOutput, $this->unixFormatter->stringForValue($this->timestamp));
    }

    /**
     * @dataProvider sensitiveDataFormatterDataProvider
     */
    function testSensitiveDataFormatter($input, $redactedChr, $showBeginCharacters, $showEndCharacters, $expectedOutput)
    {
        $f = new WFSensitiveDataFormatter;
        $f->setValuesForKeys(array(
            'redactedChr'         => $redactedChr,
            'showBeginCharacters' => $showBeginCharacters,
            'showEndCharacters'   => $showEndCharacters,
        ));
        $this->assertEquals($expectedOutput, $f->stringForValue($input));
    }
    function sensitiveDataFormatterDataProvider()
    {
        return array(
            //    input string          redaction chr       begin chars         end chars       expected output
            array('',                   'X',                0,                  0,              ''),
            array('',                   'X',                0,                  2,              ''),
            array('',                   'X',                2,                  0,              ''),

            array('123456789',          'X',                0,                  0,              'XXXXXXXXX'),
            array('123456789',          'X',                0,                  4,              'XXXXX6789'),
            array('123456789',          'X',                4,                  4,              '1234X6789'),
            array('123456789',          'X',                4,                  0,              '1234XXXXX'),
            array('123456789',          '-',                2,                  2,              '12-----89'),
        );
    }

}

?>
