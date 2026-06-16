# Componenta MIME Type Detector

Определение MIME-типа и расширения файла с объектами значения и локальной MIME-картой.

## Установка

```bash
composer require componenta/mimetype-detector
```

Пакет объявляет `Componenta\Detector\ConfigProvider` в `extra.componenta.config-providers`.
Если установлен `componenta/composer-plugin`, провайдер автоматически добавляется в сгенерированный список провайдеров.

## Требования

- PHP 8.4+
- Расширение `fileinfo`
- PSR-7 stream interfaces из `psr/http-message`

## Связанные пакеты

Пакет самодостаточный, но требует PHP-расширение `fileinfo`.

| Пакет | Зачем может использоваться рядом |
|---|---|
| `componenta/validation` | MIME-правила могут использовать detector для проверки загруженных файлов. |
| `componenta/http-responder` | Файловые ответы могут использовать MIME-тип при выдаче контента. |
| `componenta/image-converter` | Медиа-сценарии могут определять формат до конвертации. |

## Что предоставляет пакет

- `MimeType`: объект значения для разобранного MIME-типа.
- `Ext`: объект значения для нормализованного расширения файла.
- `MimeMapInterface` и `MimeMap`: карта MIME -> extensions и extension -> MIME.
- `FinfoDetector`: detector на основе `fileinfo` для строк, stream-объектов и файлов.
- `DetectorInterface`: общий контракт для определения MIME-типа, расширения, MIME-типа файла и расширения файла.
- Более узкие контракты: `MimeTypeDetectorInterface`, `ExtensionDetectorInterface`, `FileMimeTypeDetectorInterface` и `FileExtensionDetectorInterface`.
- `ConfigProvider`: регистрирует стандартный detector и MIME-карту в Componenta-приложениях.
- Типизированные исключения для отсутствующих/нечитаемых файлов и некорректных MIME-типов.

## MIME-типы

```php
use Componenta\Detector\MimeType;

$mime = new MimeType('text/html; charset=utf-8');

$mime->value;      // text/html
$mime->type;       // text
$mime->subtype;    // html
$mime->charset;    // utf-8
$mime->isText();   // true
$mime->isWebSafe(); // true
```

Некорректные MIME-строки выбрасывают `InvalidMimeTypeException`.

## Расширения

```php
use Componenta\Detector\Ext;

$ext = new Ext('.JPG');

$ext->value;    // jpg
$ext->withDot(); // .jpg
$ext->isImage(); // true
```

## MIME-карта

```php
use Componenta\Detector\MimeMap;

$map = new MimeMap();

$map->getExtension('image/jpeg'); // jpg
$map->getMimeType('jpg');         // image/jpeg

$map->extend(['application/x-custom' => ['custom']]);
```

## Определение типа

```php
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeTypeDetectorInterface;

/** @var MimeTypeDetectorInterface $detector */
$detector = new FinfoDetector();

$detector->detectMimeType('plain text'); // text/plain
$detector->detectExtension('plain text'); // txt

$detector->detectFileMimeType('/path/to/file.txt');
$detector->detectFileExtension('/path/to/file.txt');
```

Передайте `asObject: true`, чтобы вернуть объекты `MimeType` или `Ext`.

`FinfoDetector` читает до 8192 байт из файлов и stream-объектов. Seekable stream будет перемотан для определения типа, а затем возвращён на прежнюю позицию. Non-seekable stream читается с текущей позиции, поэтому вызывающий код должен заранее установить корректный offset.

Определение по файлу выбрасывает `FileNotFoundException`, `FileNotReadableException` или `DetectorException` при ошибках ввода-вывода.
