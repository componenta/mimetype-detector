<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Exception\InvalidMimeTypeException;
use Componenta\Detector\MimeParameters;
use Componenta\Detector\MimeType;
use PHPUnit\Framework\Assert;

test('MIME parameters provide case-insensitive read-only collection access', function (): void {
    $mime = new MimeType('multipart/form-data; Charset=UTF-8; BOUNDARY="Case-Sensitive"; empty=""; 123=x; zero=0');
    $parameters = $mime->parameters;

    Assert::assertInstanceOf(MimeParameters::class, $parameters);
    Assert::assertTrue($parameters->has('CHARSET'));
    Assert::assertSame('utf-8', $parameters->get('Charset'));
    Assert::assertSame('Case-Sensitive', $parameters['boundary']);
    Assert::assertSame('utf-8', $mime->charset);
    Assert::assertSame('Case-Sensitive', $mime->boundary);
    Assert::assertTrue(isset($parameters['EMPTY']));
    Assert::assertSame('', $parameters->get('empty'));
    Assert::assertTrue($parameters->has('ZERO'));
    Assert::assertSame('0', $parameters['ZERO']);
    Assert::assertFalse($parameters->has('missing'));
    Assert::assertNull($parameters->get('missing'));
    Assert::assertFalse(isset($parameters['missing']));
    Assert::assertNull($parameters['missing']);
    Assert::assertTrue(isset($parameters[123]));
    Assert::assertSame('x', $parameters['123']);
    Assert::assertSame('x', $parameters[123]);
    Assert::assertFalse(isset($parameters[true]));
    Assert::assertNull($parameters[true]);
    Assert::assertFalse(isset($parameters[[]]));
    Assert::assertNull($parameters[[]]);
    Assert::assertFalse($parameters->isEmpty());
    Assert::assertCount(5, $parameters);

    $entries = [];
    foreach ($parameters as $name => $value) {
        $entries[] = [$name, $value];
    }
    Assert::assertSame([
        ['charset', 'utf-8'], ['boundary', 'Case-Sensitive'], ['empty', ''],
        ['123', 'x'], ['zero', '0'],
    ], $entries);
    Assert::assertSame([
        'charset' => 'utf-8', 'boundary' => 'Case-Sensitive', 'empty' => '',
        123 => 'x', 'zero' => '0',
    ], iterator_to_array($parameters));
    Assert::assertSame(iterator_to_array($parameters), iterator_to_array($parameters));
});

test('an unparameterized MIME type exposes an empty collection', function (): void {
    $mime = new MimeType('image/png');
    Assert::assertTrue($mime->parameters->isEmpty());
    Assert::assertCount(0, $mime->parameters);
    Assert::assertSame([], iterator_to_array($mime->parameters));
    Assert::assertNull($mime->charset);
    Assert::assertNull($mime->boundary);
});

test('standalone construction normalizes and snapshots decoded parameters', function (): void {
    $boundary = 'Case';
    $input = ['CHARSET' => 'UTF-8', 'Boundary' => &$boundary, 'other' => 'a;b"c'];
    $parameters = new MimeParameters($input);
    $input['CHARSET'] = 'latin1';
    $boundary = 'Changed';

    Assert::assertSame('utf-8', $parameters->get('charset'));
    Assert::assertSame('Case', $parameters->get('boundary'));
    Assert::assertSame('a;b"c', $parameters->get('other'));
    $snapshot = iterator_to_array($parameters);
    $snapshot['boundary'] = 'Changed again';
    Assert::assertSame('Case', $parameters->get('boundary'));
});

test('standalone parameters reject invalid names, values and duplicates', function (array $parameters): void {
    $this->expectException(InvalidMimeTypeException::class);
    new MimeParameters($parameters);
})->with([
    'duplicate' => [['Charset' => 'a', 'CHARSET' => 'b']],
    'empty name' => [['' => 'value']],
    'invalid name' => [['bad name' => 'value']],
    'control value' => [['name' => "value\r\n"]],
    'non-string value' => [['name' => 1]],
    'null value' => [['name' => null]],
]);

test('array writes and unsets cannot mutate parameters', function (string $operation): void {
    $parameters = (new MimeType('text/plain; charset=UTF-8'))->parameters;
    try {
        match ($operation) {
            'set' => $parameters['charset'] = 'latin1',
            'append' => $parameters[] = 'latin1',
            'unset' => (static function () use ($parameters): void { unset($parameters['charset']); })(),
        };
        Assert::fail('Expected immutable collection failure.');
    } catch (\LogicException) {
        Assert::assertSame('utf-8', $parameters['charset']);
        Assert::assertCount(1, $parameters);
    }
})->with(['set', 'append', 'unset']);
