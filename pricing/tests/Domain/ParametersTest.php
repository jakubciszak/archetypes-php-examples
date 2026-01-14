<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use Brick\Math\BigDecimal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\Parameters;

final class ParametersTest extends TestCase
{
    public function testCanCreateEmptyParameters(): void
    {
        $parameters = Parameters::empty();

        $this->assertInstanceOf(Parameters::class, $parameters);
    }

    public function testCanCreateParametersFromArray(): void
    {
        $parameters = new Parameters(['key' => 'value']);

        $this->assertTrue($parameters->contains('key'));
    }

    public function testContainsReturnsTrueWhenKeyExists(): void
    {
        $parameters = new Parameters(['key' => 'value']);

        $this->assertTrue($parameters->contains('key'));
    }

    public function testContainsReturnsFalseWhenKeyDoesNotExist(): void
    {
        $parameters = new Parameters(['key' => 'value']);

        $this->assertFalse($parameters->contains('other'));
    }

    public function testContainsAllReturnsTrueWhenAllKeysExist(): void
    {
        $parameters = new Parameters([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $this->assertTrue($parameters->containsAll(['key1', 'key2']));
    }

    public function testContainsAllReturnsFalseWhenSomeKeysMissing(): void
    {
        $parameters = new Parameters([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertFalse($parameters->containsAll(['key1', 'key3']));
    }

    public function testGetReturnsValue(): void
    {
        $parameters = new Parameters(['key' => 'value']);

        $this->assertEquals('value', $parameters->get('key'));
    }

    public function testGetBigDecimalFromBigDecimal(): void
    {
        $decimal = BigDecimal::of('123.45');
        $parameters = new Parameters(['amount' => $decimal]);

        $result = $parameters->getBigDecimal('amount');

        $this->assertTrue($result->isEqualTo($decimal));
    }

    public function testGetBigDecimalFromNumber(): void
    {
        $parameters = new Parameters(['amount' => 123]);

        $result = $parameters->getBigDecimal('amount');

        $this->assertTrue($result->isEqualTo(BigDecimal::of('123')));
    }

    public function testGetBigDecimalFromString(): void
    {
        $parameters = new Parameters(['amount' => '123.45']);

        $result = $parameters->getBigDecimal('amount');

        $this->assertTrue($result->isEqualTo(BigDecimal::of('123.45')));
    }

    public function testGetBigDecimalThrowsExceptionForInvalidValue(): void
    {
        $parameters = new Parameters(['amount' => []]);

        $this->expectException(InvalidArgumentException::class);
        $parameters->getBigDecimal('amount');
    }

    public function testKeysReturnsAllKeys(): void
    {
        $parameters = new Parameters([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $keys = $parameters->keys();

        $this->assertCount(2, $keys);
        $this->assertContains('key1', $keys);
        $this->assertContains('key2', $keys);
    }

    public function testGetValuesReturnsAllValues(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $parameters = new Parameters($data);

        $values = $parameters->getValues();

        $this->assertEquals($data, $values);
    }

    public function testSetValuesReplacesAllValues(): void
    {
        $parameters = new Parameters(['key1' => 'value1']);
        $newValues = ['key2' => 'value2'];

        $parameters->setValues($newValues);

        $this->assertFalse($parameters->contains('key1'));
        $this->assertTrue($parameters->contains('key2'));
    }
}
