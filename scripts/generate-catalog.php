<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/src/Exception/InvalidMimeTypeException.php';
require_once $root . '/src/Internal/MediaTypeParser.php';
require_once $root . '/src/Ext.php';
$catalog = json_decode(file_get_contents($root . '/resources/mime-catalog.json'), true, flags: JSON_THROW_ON_ERROR);
if (!is_array($catalog) || !isset($catalog['types']) || !is_array($catalog['types']) || $catalog['types'] === []) {
    throw new RuntimeException('Expected a nonempty MIME catalog.');
}
$types = $catalog['types'];
$check = in_array('--check', $argv, true);
$failed = false;
$enums = [];
$map = [];
$generated = [];

$write = static function (string $path, string $content) use ($root, $check, &$failed): void {
    $path = $root . '/' . $path;
    if ($check) {
        if (!is_file($path) || file_get_contents($path) !== $content) {
            fwrite(STDERR, "Generated file is out of date: $path\n");
            $failed = true;
        }
        return;
    }

    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Unable to write $path");
    }
};

foreach ($types as $mime => $record) {
    if (!is_string($mime) || \Componenta\Detector\Internal\MediaTypeParser::tryNormalize($mime) !== $mime) {
        throw new RuntimeException('Invalid canonical MIME type: ' . $mime);
    }
    if (!is_array($record) || $record === [] || array_diff(array_keys($record), ['extensions', 'extensionAliasOf', 'enums']) !== []) {
        throw new RuntimeException("Invalid record for $mime");
    }
    if (array_key_exists('extensions', $record) && !is_array($record['extensions'])) {
        throw new RuntimeException("Invalid extension list for $mime");
    }
    if (array_key_exists('extensionAliasOf', $record) && !is_string($record['extensionAliasOf'])) {
        throw new RuntimeException("Invalid extension alias for $mime");
    }
    if (array_key_exists('enums', $record) && (!is_array($record['enums']) || $record['enums'] === [])) {
        throw new RuntimeException("Invalid enum definitions for $mime");
    }
    if (isset($record['extensions']) && isset($record['extensionAliasOf'])) {
        throw new RuntimeException("Conflicting extension definitions for $mime");
    }
    if (isset($record['extensionAliasOf']) && !isset($types[$record['extensionAliasOf']]['extensions'])) {
        throw new RuntimeException("Unknown or noncanonical extension alias target for $mime");
    }
    if (isset($record['extensionAliasOf']) && !array_key_exists($record['extensionAliasOf'], $map)) {
        throw new RuntimeException("Extension alias must follow its canonical type: $mime");
    }
    $extensions = $record['extensions'] ?? (isset($record['extensionAliasOf']) ? $types[$record['extensionAliasOf']]['extensions'] : null);
    if ($extensions !== null) {
        if (!is_array($extensions) || !array_is_list($extensions)) {
            throw new RuntimeException("Invalid extension list for $mime");
        }
        foreach ($extensions as $extension) {
            if (!is_string($extension) || \Componenta\Detector\Ext::tryCreate($extension)?->value !== $extension) {
                throw new RuntimeException("Invalid extension for $mime");
            }
        }
        if (count($extensions) !== count(array_unique($extensions))) {
            throw new RuntimeException("Duplicate extension for $mime");
        }
        $map[$mime] = $extensions;
    }

    foreach ($record['enums'] ?? [] as $enum => $cases) {
        if (!in_array($enum, ['Application', 'Audio', 'Font', 'Image', 'Message', 'Model', 'Multipart', 'Text', 'Video'], true)) {
            throw new RuntimeException("Unknown enum $enum");
        }
        if (!is_array($cases) || !array_is_list($cases) || $cases === []) {
            throw new RuntimeException("Invalid enum member list for $mime");
        }
        foreach ($cases as $case) {
            if (!is_array($case) || array_diff(array_keys($case), ['name', 'order', 'aliasOf']) !== []
                || (array_key_exists('aliasOf', $case) && !is_string($case['aliasOf']))) {
                throw new RuntimeException("Invalid enum member for $mime");
            }
            $name = $case['name'] ?? null;
            $order = $case['order'] ?? null;
            if (!is_string($name) || preg_match('/\A[A-Za-z_][A-Za-z0-9_]*\z/D', $name) !== 1 || !is_int($order) || $order < 0 || isset($enums[$enum][$order])) {
                throw new RuntimeException("Invalid enum case for $mime");
            }
            if (strtolower($name) === 'class') {
                throw new RuntimeException("Reserved enum member name for $mime");
            }
            if (!str_starts_with($mime, strtolower($enum) . '/') && !($enum === 'Font' && str_starts_with($mime, 'application/'))) {
                throw new RuntimeException("MIME type $mime does not belong to enum $enum");
            }
            $enums[$enum][$order] = ['name' => $name, 'mime' => $mime, 'aliasOf' => $case['aliasOf'] ?? null];
        }
    }
}

$lines = [];
foreach ($map as $mime => $extensions) {
    $lines[] = '        ' . var_export($mime, true) . ' => [' . implode(', ', array_map(static fn (string $extension): string => var_export($extension, true), $extensions)) . '],';
}
$header = <<<'PHP'
<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

/** @internal Generated by scripts/generate-catalog.php; do not edit. */
final class DefaultMimeMap
{
    /** @var array<string, list<string>> */
    public const array MAPPINGS = [
PHP;
$generated['src/Internal/DefaultMimeMap.php'] = $header . "\n" . implode("\n", $lines) . "\n    ];\n}\n";

foreach (['Application', 'Audio', 'Font', 'Image', 'Message', 'Model', 'Multipart', 'Text', 'Video'] as $enum) {
    $cases = $enums[$enum] ?? throw new RuntimeException("Missing enum $enum from catalog");
    ksort($cases, SORT_NUMERIC);
    $names = [];
    $values = [];
    $lines = [];
    foreach ($cases as $case) {
        $name = $case['name'];
        $mime = $case['mime'];
        $alias = $case['aliasOf'];
        if (isset($names[$name])) {
            throw new RuntimeException("Duplicate enum member $enum::$name");
        }
        if ($alias === null) {
            if (isset($values[$mime])) {
                throw new RuntimeException("Duplicate enum value $enum::$name");
            }
            $values[$mime] = $name;
            $lines[] = '    case ' . $name . ' = ' . var_export($mime, true) . ';';
        } else {
            if (($values[$mime] ?? null) !== $alias) {
                throw new RuntimeException("Invalid enum alias $enum::$name");
            }
            $lines[] = '    public const ' . $name . ' = self::' . $alias . ';';
        }
        $names[$name] = true;
    }

    $path = 'src/MimeTypes/' . $enum . '.php';
    $source = file_get_contents($root . '/' . $path);
    $begin = "    // BEGIN GENERATED MIME CASES\n";
    $end = "    // END GENERATED MIME CASES\n";
    $start = strpos($source, $begin);
    $stop = strpos($source, $end);
    if ($start === false || $stop === false || $stop <= $start) {
        throw new RuntimeException("Missing generated section in $path");
    }
    $generated[$path] = substr($source, 0, $start) . $begin . implode("\n", $lines) . "\n" . substr($source, $stop);
}

// Validate the complete catalog and all generated sections before writing any file.
foreach ($generated as $path => $content) {
    $write($path, $content);
}

exit($failed ? 1 : 0);
