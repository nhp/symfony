<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class SimpleChoiceListTest extends \PHPUnit_Framework_TestCase
{
    private $list;

    private $numericList;

    protected function setUp()
    {
        parent::setUp();

        $choices = array(
            'Group 1' => array('a' => 'A', 'b' => 'B'),
            'Group 2' => array('c' => 'C', 'd' => 'D'),
        );
        $numericChoices = array(
            'Group 1' => array(0 => 'A', 1 => 'B'),
            'Group 2' => array(2 => 'C', 3 => 'D'),
        );

        $this->list = new SimpleChoiceList($choices, array('b', 'c'), ChoiceList::GENERATE, ChoiceList::GENERATE);

        // Use COPY_CHOICE strategy to test for the various associated problems
        $this->numericList = new SimpleChoiceList($numericChoices, array(1, 2), ChoiceList::COPY_CHOICE, ChoiceList::GENERATE);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->list = null;
        $this->numericList = null;
    }

    public function testInitArray()
    {
        $choices = array('a' => 'A', 'b' => 'B', 'c' => 'C');
        $this->list = new SimpleChoiceList($choices, array('b'), ChoiceList::GENERATE, ChoiceList::GENERATE);

        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getChoices());
        $this->assertSame(array(0 => '0', 1 => '1', 2 => '2'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('0', 'A'), 2 => new ChoiceView('2', 'C')), $this->list->getRemainingViews());
    }

    public function testInitArrayValueCopyChoice()
    {
        $choices = array('a' => 'A', 'b' => 'B', 'c' => 'C');
        $this->list = new SimpleChoiceList($choices, array('b'), ChoiceList::COPY_CHOICE, ChoiceList::GENERATE);

        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('b', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('a', 'A'), 2 => new ChoiceView('c', 'C')), $this->list->getRemainingViews());
    }

    public function testInitArrayIndexCopyChoice()
    {
        $choices = array('a' => 'A', 'b' => 'B', 'c' => 'C');
        $this->list = new SimpleChoiceList($choices, array('b'), ChoiceList::GENERATE, ChoiceList::COPY_CHOICE);

        $this->assertSame(array('a' => 'a', 'b' => 'b', 'c' => 'c'), $this->list->getChoices());
        $this->assertSame(array('a' => '0', 'b' => '1', 'c' => '2'), $this->list->getValues());
        $this->assertEquals(array('b' => new ChoiceView('1', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array('a' => new ChoiceView('0', 'A'), 'c' => new ChoiceView('2', 'C')), $this->list->getRemainingViews());
    }

    public function testInitNestedArray()
    {
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd'), $this->list->getChoices());
        $this->assertSame(array(0 => '0', 1 => '1', 2 => '2', 3 => '3'), $this->list->getValues());
        $this->assertEquals(array(
            'Group 1' => array(1 => new ChoiceView('1', 'B')),
            'Group 2' => array(2 => new ChoiceView('2', 'C'))
        ), $this->list->getPreferredViews());
        $this->assertEquals(array(
            'Group 1' => array(0 => new ChoiceView('0', 'A')),
            'Group 2' => array(3 => new ChoiceView('3', 'D'))
        ), $this->list->getRemainingViews());
    }

    public function testGetIndicesForChoices()
    {
        $choices = array('b', 'c');
        $this->assertSame(array(1, 2), $this->list->getIndicesForChoices($choices));
    }

    public function testGetIndicesForChoicesIgnoresNonExistingChoices()
    {
        $choices = array('b', 'c', 'foobar');
        $this->assertSame(array(1, 2), $this->list->getIndicesForChoices($choices));
    }

    public function testGetIndicesForChoicesDealsWithNumericChoices()
    {
        // Pass choices as strings although they are integers
        $choices = array('0', '1');
        $this->assertSame(array(0, 1), $this->numericList->getIndicesForChoices($choices));
    }

    public function testGetIndicesForValues()
    {
        $values = array('1', '2');
        $this->assertSame(array(1, 2), $this->list->getIndicesForValues($values));
    }

    public function testGetIndicesForValuesIgnoresNonExistingValues()
    {
        $values = array('1', '2', '100');
        $this->assertSame(array(1, 2), $this->list->getIndicesForValues($values));
    }

    public function testGetIndicesForValuesDealsWithNumericValues()
    {
        // Pass values as strings although they are integers
        $values = array('0', '1');
        $this->assertSame(array(0, 1), $this->numericList->getIndicesForValues($values));
    }

    public function testGetChoicesForValues()
    {
        $values = array('1', '2');
        $this->assertSame(array('b', 'c'), $this->list->getChoicesForValues($values));
    }

    public function testGetChoicesForValuesIgnoresNonExistingValues()
    {
        $values = array('1', '2', '100');
        $this->assertSame(array('b', 'c'), $this->list->getChoicesForValues($values));
    }

    public function testGetChoicesForValuesDealsWithNumericValues()
    {
        // Pass values as strings although they are integers
        $values = array('0', '1');
        $this->assertSame(array(0, 1), $this->numericList->getChoicesForValues($values));
    }

    public function testGetValuesForChoices()
    {
        $choices = array('b', 'c');
        $this->assertSame(array('1', '2'), $this->list->getValuesForChoices($choices));
    }

    public function testGetValuesForChoicesIgnoresNonExistingValues()
    {
        $choices = array('b', 'c', 'foobar');
        $this->assertSame(array('1', '2'), $this->list->getValuesForChoices($choices));
    }

    public function testGetValuesForChoicesDealsWithNumericValues()
    {
        // Pass values as strings although they are integers
        $values = array('0', '1');

        $this->assertSame(array('0', '1'), $this->numericList->getValuesForChoices($values));
    }

    /**
     * @dataProvider dirtyValuesProvider
     */
    public function testGetValuesForChoicesDealsWithDirtyValues($choice, $value)
    {
        $choices = array(
            '0' => 'Zero',
            '1' => 'One',
            '' => 'Empty',
            '1.23' => 'Float',
            'foo' => 'Foo',
            'foo10' => 'Foo 10',
        );

        // use COPY_CHOICE strategy to test the problems
        $this->list = new SimpleChoiceList($choices, array(), ChoiceList::COPY_CHOICE, ChoiceList::GENERATE);

        $this->assertSame(array($value), $this->list->getValuesForChoices(array($choice)));
    }

    public function dirtyValuesProvider()
    {
        return array(
            array(0, '0'),
            array('0', '0'),
            array('1', '1'),
            array(false, '0'),
            array(true, '1'),
            array('', ''),
            array(null, ''),
            array('1.23', '1.23'),
            array('foo', 'foo'),
            array('foo10', 'foo10'),
        );
    }
}
