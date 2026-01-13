<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Common;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Result;

class ResultTest extends TestCase
{
    public function testSuccessCreation(): void
    {
        $result = Result::success('success value');

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertEquals('success value', $result->getSuccess());
    }

    public function testFailureCreation(): void
    {
        $result = Result::failure('failure value');

        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('failure value', $result->getFailure());
    }

    public function testMapOnSuccess(): void
    {
        $result = Result::success(10);

        $mapped = $result->map(fn($x) => $x * 2);

        $this->assertTrue($mapped->isSuccess());
        $this->assertEquals(20, $mapped->getSuccess());
    }

    public function testMapOnFailure(): void
    {
        $result = Result::failure('error');

        $mapped = $result->map(fn($x) => $x * 2);

        $this->assertTrue($mapped->isFailure());
        $this->assertEquals('error', $mapped->getFailure());
    }

    public function testMapFailureOnSuccess(): void
    {
        $result = Result::success('success');

        $mapped = $result->mapFailure(fn($x) => strtoupper($x));

        $this->assertTrue($mapped->isSuccess());
        $this->assertEquals('success', $mapped->getSuccess());
    }

    public function testMapFailureOnFailure(): void
    {
        $result = Result::failure('error');

        $mapped = $result->mapFailure(fn($x) => strtoupper($x));

        $this->assertTrue($mapped->isFailure());
        $this->assertEquals('ERROR', $mapped->getFailure());
    }

    public function testFlatMapOnSuccess(): void
    {
        $result = Result::success(10);

        $flatMapped = $result->flatMap(fn($x) => Result::success($x * 2));

        $this->assertTrue($flatMapped->isSuccess());
        $this->assertEquals(20, $flatMapped->getSuccess());
    }

    public function testFlatMapOnFailure(): void
    {
        $result = Result::failure('error');

        $flatMapped = $result->flatMap(fn($x) => Result::success($x * 2));

        $this->assertTrue($flatMapped->isFailure());
        $this->assertEquals('error', $flatMapped->getFailure());
    }

    public function testFold(): void
    {
        $success = Result::success(10);
        $failure = Result::failure('error');

        $successResult = $success->fold(
            fn($x) => "Success: $x",
            fn($x) => "Failure: $x"
        );

        $failureResult = $failure->fold(
            fn($x) => "Success: $x",
            fn($x) => "Failure: $x"
        );

        $this->assertEquals('Success: 10', $successResult);
        $this->assertEquals('Failure: error', $failureResult);
    }

    public function testPeekOnSuccess(): void
    {
        $called = false;
        $result = Result::success('value');

        $result->peek(function ($x) use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    public function testPeekOnFailure(): void
    {
        $called = false;
        $result = Result::failure('error');

        $result->peek(function ($x) use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function testPeekFailureOnSuccess(): void
    {
        $called = false;
        $result = Result::success('value');

        $result->peekFailure(function ($x) use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function testPeekFailureOnFailure(): void
    {
        $called = false;
        $result = Result::failure('error');

        $result->peekFailure(function ($x) use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    public function testBiMap(): void
    {
        $success = Result::success(10);
        $failure = Result::failure('error');

        $mappedSuccess = $success->biMap(
            fn($x) => $x * 2,
            fn($x) => strtoupper($x)
        );

        $mappedFailure = $failure->biMap(
            fn($x) => $x * 2,
            fn($x) => strtoupper($x)
        );

        $this->assertTrue($mappedSuccess->isSuccess());
        $this->assertEquals(20, $mappedSuccess->getSuccess());

        $this->assertTrue($mappedFailure->isFailure());
        $this->assertEquals('ERROR', $mappedFailure->getFailure());
    }
}
