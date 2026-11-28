# Componenta MIME Type Detector

[English](README.md)

Определение MIME-типа и расширения по содержимому с настраиваемым справочником расширений.

## Установка

```sh
composer require componenta/mimetype-detector:^2.0
```

Документация для обновлённой редакции версии 2.0.0. Требуются PHP 8.4+, ext-fileinfo, PSR-7 2.x и Componenta Config 4.x. Контейнер зависимостей для обычного использования не нужен. [Руководство по миграции](UPGRADE.md).

## Быстрый старт

```php
use Componenta\Detector\FinfoDetector;

$detector = new FinfoDetector();
$mime = $detector->detectMimeType('plain text');
$extension = $detector->detectExtension('plain text');

echo $mime?->value;       // text/plain
echo $extension?->value;  // txt
```

Для локального файла используйте `detectFileMimeType($path)` или `detectFileExtension($path)`. Строка в методах определения по содержимому всегда означает байты, а не путь.

## Определение типа и расширения

### [`FinfoDetector`](src/FinfoDetector.php)

Стандартная реализация всех четырёх контрактов определения типа и расширения. Можно использовать напрямую или через контейнер.

#### `__construct()`

`__construct(?ExtensionResolverInterface $mimeMap = null, int $maxBytes = 8192)`

Создаёт детектор.

| Аргумент | Описание |
| --- | --- |
| `$mimeMap` | Источник предпочтительного расширения. При `null` используется стандартная карта `MimeMap`. |
| `$maxBytes` | Положительный предел числа анализируемых байтов для любого источника. |

**Исключения:** `DetectorConfigurationException` при недопустимом ограничении или сбое инициализации Fileinfo.


#### `detectMimeType()`

`detectMimeType(string|StreamInterface $content): ?MimeType`

Определяет MIME-тип по содержимому.

| Аргумент | Описание |
| --- | --- |
| `$content` | Байты содержимого или поток PSR-7. Строка не является путём. |

**Возвращает:** `MimeType` или `null`, если результат отсутствует.

**Исключения:** `DetectorException`, `StreamExceptionInterface`.


#### `detectFileMimeType()`

`detectFileMimeType(string $filename): ?MimeType`

Определяет MIME-тип по начальной части локального файла.

| Аргумент | Описание |
| --- | --- |
| `$filename` | Разрешённый приложением путь к обычному локальному файлу. |

**Возвращает:** `MimeType` или `null`, если результат отсутствует.

**Исключения:** `DetectorException`, `FileExceptionInterface`.


#### `detectExtension()`

`detectExtension(string|StreamInterface $content): ?Ext`

Определяет предпочтительное расширение обнаруженного MIME-типа по настроенной карте.

| Аргумент | Описание |
| --- | --- |
| `$content` | Байты содержимого или поток PSR-7. Строка не является путём. |

**Возвращает:** `Ext` или `null`, если тип не определён или расширение отсутствует в карте.

**Исключения:** `DetectorExceptionInterface`, `StreamExceptionInterface`.


#### `detectFileExtension()`

`detectFileExtension(string $filename): ?Ext`

Определяет предпочтительное расширение по содержимому локального файла. Имя файла не используется для распознавания.

| Аргумент | Описание |
| --- | --- |
| `$filename` | Разрешённый приложением путь к обычному локальному файлу. |

**Возвращает:** `Ext` или `null`, если тип не определён или расширение отсутствует в карте.

**Исключения:** `DetectorExceptionInterface`, `FileExceptionInterface`.


### Контракты

| Интерфейс | Операции |
| --- | --- |
| [MimeTypeDetectorInterface](src/MimeTypeDetectorInterface.php) | `detectMimeType()` |
| [FileMimeTypeDetectorInterface](src/FileMimeTypeDetectorInterface.php) | `detectFileMimeType()` |
| [ExtensionDetectorInterface](src/ExtensionDetectorInterface.php) | `detectExtension()` |
| [FileExtensionDetectorInterface](src/FileExtensionDetectorInterface.php) | `detectFileExtension()` |
| [DetectorInterface](src/DetectorInterface.php) | Все четыре операции |

Зависите от нужного узкого контракта. Пользовательские реализации сообщают об ошибках определения через `DetectorExceptionInterface`, а об ошибках получения источника — через соответствующий файловый или потоковый интерфейс. Ошибки потоков не относятся к строковым входным данным.

### Источники и ограничения

Все источники анализируются в пределах `maxBytes`. Поток с поддержкой позиционирования читается с начала, исходная позиция восстанавливается при успехе и ошибке. Поток без поддержки позиционирования потребляется с текущей позиции не более чем на анализируемый префикс. Поток остаётся во владении вызывающей стороны. При невозможности восстановления позиции выбрасывается отдельное исключение.

Локальные файлы не изменяются. URL, обёртки потоков, каталоги и специальные файлы отклоняются. Символические ссылки на обычные файлы разрешены; приложение отвечает за авторизацию пути. Чтение ограниченного префикса не является гарантией полного формата, безопасности, времени обработки или абсолютного объёма памяти. Используйте читаемые блокирующие потоки с подходящими ограничениями времени ожидания.

Пустое содержимое даёт `null`. Для неизвестного формата fileinfo может вернуть `application/octet-stream`. Результат эвристический и зависит от версии PHP/libmagic и исследованного префикса.

## [`MimeType`](src/MimeType.php)

```php
use Componenta\Detector\MimeType;

$mime = new MimeType('Text/HTML; charset=UTF-8; name="a;b.html"');
echo $mime->value; // text/html
$mime->equals('text/html'); // true
$mime->matches('text/*');   // true
```

Неизменяемый объект конкретного MIME-типа по грамматике RFC 6838 и синтаксису параметров RFC 9110.

| Свойство | Описание |
| --- | --- |
| `value: string` | Нормализованный тип и подтип без параметров. |
| `type: string` | Основной тип. |
| `subtype: string` | Подтип. |
| `parameters: array` | Параметры с нормализованными именами; регистр значений сохраняется, кроме charset. |
| `raw: string` | Исходная строка. |
| `charset: ?string` | Кодировка или null. |
| `boundary: ?string` | Граница multipart или null. |

#### `__construct()`

`__construct(string $value)`

Проверяет и нормализует конкретный MIME-тип. Поддерживает параметры в кавычках и экранирование; отклоняет дубликаты, шаблоны и недопустимые управляющие символы.

| Аргумент | Описание |
| --- | --- |
| `$value` | Строка MIME-типа с необязательными параметрами. |

**Исключения:** `InvalidMimeTypeException`.


#### `tryCreate()`

`tryCreate(string $value): ?self`

Статический метод безопасного создания объекта.

| Аргумент | Описание |
| --- | --- |
| `$value` | Проверяемая строка. |

**Возвращает:** Объект или `null` при некорректном значении.


#### `isValid()`

`isValid(string $value): bool`

Статический метод проверки того же формата, что принимает конструктор.

| Аргумент | Описание |
| --- | --- |
| `$value` | Проверяемая строка. |

**Возвращает:** `true`, если значение допустимо.


#### `hasParameters()`

`hasParameters(): bool`

Проверяет наличие параметров MIME-типа.

**Возвращает:** `true`, если параметры присутствуют.


#### `getParameter()`

`getParameter(string $name): ?string`

Получает параметр без учёта регистра имени.

| Аргумент | Описание |
| --- | --- |
| `$name` | Имя параметра. |

**Возвращает:** Значение или `null`.


#### `equals()`

`equals(self|string ...$types): bool`

Сравнивает нормализованные тип и подтип, игнорируя параметры. Шаблоны не являются точными типами.

| Аргумент | Описание |
| --- | --- |
| `$types` | Ноль или несколько сравниваемых типов. |

**Возвращает:** `true` при совпадении хотя бы одного значения; иначе `false`.


#### `matches()`

`matches(string ...$ranges): bool`

Сопоставляет точный тип без параметров, шаблон `image/*` или `*/*`. Не разбирает заголовки Accept и коэффициенты качества.

| Аргумент | Описание |
| --- | --- |
| `$ranges` | Допустимые типы и шаблоны. |

**Возвращает:** `true` при хотя бы одном совпадении.


#### Классификация и перечисления

Методы классификации проверяют имя MIME-типа, а не содержимое. Все методы ниже не принимают аргументов.

| Сигнатура | Результат |
| --- | --- |
| `__toString(): string` | Тип и подтип без параметров. |
| `isImage(): bool` | Основной тип — `image`. |
| `isAudio(): bool` | Основной тип — `audio`. |
| `isVideo(): bool` | Основной тип — `video`. |
| `isText(): bool` | Основной тип — `text`. |
| `isApplication(): bool` | Основной тип — `application`. |
| `isModel(): bool` | Основной тип — `model`. |
| `isMessage(): bool` | Основной тип — `message`. |
| `isMultipart(): bool` | Основной тип — `multipart`. |
| `isFont(): bool` | Шрифт, включая известные устаревшие application/font обозначения. |
| `isMedia(): bool` | Изображение, видео или аудио. |
| `toImageEnum(): ?Image` | Соответствующий элемент `Image` или null. |
| `toAudioEnum(): ?Audio` | Соответствующий элемент `Audio` или null. |
| `toVideoEnum(): ?Video` | Соответствующий элемент `Video` или null. |
| `toTextEnum(): ?Text` | Соответствующий элемент `Text` или null. |
| `toApplicationEnum(): ?Application` | Соответствующий элемент `Application` или null. |
| `toFontEnum(): ?Font` | Соответствующий элемент `Font` или null. |
| `toModelEnum(): ?Model` | Соответствующий элемент `Model` или null. |
| `toMessageEnum(): ?Message` | Соответствующий элемент `Message` или null. |
| `toMultipartEnum(): ?Multipart` | Соответствующий элемент `Multipart` или null. |

## [`Ext`](src/Ext.php)

```php
use Componenta\Detector\Ext;

$ext = new Ext('.TAR.GZ');
echo $ext->value;     // tar.gz
echo $ext->withDot(); // .tar.gz
```

Неизменяемое расширение. Свойство `value: string` содержит нормализованный суффикс без ведущей точки.

#### `__construct()`

`__construct(string $value)`

Принимает простое или составное ASCII-расширение с необязательной ведущей точкой. Пути и пустые значения недопустимы; метод не извлекает расширение из имени файла.

| Аргумент | Описание |
| --- | --- |
| `$value` | Расширение. |

**Исключения:** `InvalidArgumentException`.


#### `tryCreate()`

`tryCreate(string $value): ?self`

Статическое создание без исключения при некорректном расширении.

| Аргумент | Описание |
| --- | --- |
| `$value` | Расширение. |

**Возвращает:** Объект или `null`.


#### `equals()`

`equals(self|string ...$extensions): bool`

Проверяет совпадение нормализованных расширений.

| Аргумент | Описание |
| --- | --- |
| `$extensions` | Сравниваемые расширения. |

**Возвращает:** `true` при хотя бы одном совпадении.


#### `withDot()`

`withDot(): string`

Возвращает расширение с ведущей точкой.

**Возвращает:** Строка, например `.tar.gz`.


#### `__toString()`

`__toString(): string`

Возвращает нормализованное расширение без ведущей точки.

**Возвращает:** Строка, например `tar.gz`.


## [`MimeMap`](src/MimeMap.php)

Настраиваемый справочник. Поиск определён контрактом [MimeMapInterface](src/MimeMapInterface.php). Порядок важен: первое расширение является предпочтительным; обратный поиск выбирает первый MIME-тип. Изменения атомарны. Сопоставление расширения с типом не означает их семантическую эквивалентность или безопасность содержимого.

```php
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeMap;

$map = new MimeMap();
$map->extend(['application/x-custom' => ['.CUSTOM']]);
$map->getExtension('application/x-custom'); // custom
$detector = new FinfoDetector($map);
```

#### `getExtensions()`

`getExtensions(string $mimeType): array`

Получает список расширений MIME-типа.

| Аргумент | Описание |
| --- | --- |
| `$mimeType` | Конкретный MIME-тип; параметры не участвуют в поиске. |

**Возвращает:** Упорядоченный `list<string>` или пустой список.


#### `getExtension()`

`getExtension(string $mimeType): ?string`

Получает предпочтительное расширение.

| Аргумент | Описание |
| --- | --- |
| `$mimeType` | Конкретный MIME-тип; параметры не участвуют в поиске. |

**Возвращает:** Строка или `null`.


#### `getMimeType()`

`getMimeType(string $extension): ?string`

Получает первый сопоставленный MIME-тип.

| Аргумент | Описание |
| --- | --- |
| `$extension` | Расширение с необязательной ведущей точкой. |

**Возвращает:** Строка или `null`.


#### `getMimeTypes()`

`getMimeTypes(string $extension): array`

Получает все MIME-типы для расширения.

| Аргумент | Описание |
| --- | --- |
| `$extension` | Расширение с необязательной ведущей точкой. |

**Возвращает:** Упорядоченный `list<string>` или пустой список.


#### `isValidExtension()`

`isValidExtension(string $mimeType, string $extension): bool`

Проверяет наличие соответствия в справочнике.

| Аргумент | Описание |
| --- | --- |
| `$mimeType` | Конкретный MIME-тип; параметры не участвуют в поиске. |
| `$extension` | Расширение с необязательной ведущей точкой. |

**Возвращает:** `true` при наличии соответствия.


#### `hasMimeType()`

`hasMimeType(string $mimeType): bool`

Проверяет наличие типа, даже если его список расширений пуст.

| Аргумент | Описание |
| --- | --- |
| `$mimeType` | Конкретный MIME-тип; параметры не участвуют в поиске. |

**Возвращает:** `true`, если тип зарегистрирован.


#### `hasExtension()`

`hasExtension(string $extension): bool`

Проверяет наличие хотя бы одного сопоставленного типа.

| Аргумент | Описание |
| --- | --- |
| `$extension` | Расширение с необязательной ведущей точкой. |

**Возвращает:** `true`, если соответствие существует.


#### `getAllMimeTypes()`

`getAllMimeTypes(): array`

Получает все зарегистрированные типы.

**Возвращает:** `list<string>` в порядке карты.


#### `getAllExtensions()`

`getAllExtensions(): array`

Получает уникальные расширения.

**Возвращает:** `list<string>` в порядке первой регистрации.


#### `getMimeTypesByCategory()`

`getMimeTypesByCategory(string $category): array`

Получает сопоставления основного типа.

| Аргумент | Описание |
| --- | --- |
| `$category` | Например `image` или `text`. |

**Возвращает:** `array<string, list<string>>`.


#### `__construct()`

`__construct()`

Создаёт карту со стандартным локальным справочником.


#### `extend()`

`extend(array $map, bool $override = false): self`

Добавляет или объединяет соответствия; сохраняет существующий порядок.

| Аргумент | Описание |
| --- | --- |
| `$map` | Соответствия MIME-типов спискам расширений (`array<string, list<string>>`). |
| `$override` | `true` заменяет список существующего типа; по умолчанию объединяет. |

**Возвращает:** Тот же экземпляр карты.

**Исключения:** `InvalidArgumentException`.


#### `remove()`

`remove(string ...$mimeTypes): self`

Атомарно удаляет соответствия заданных типов.

| Аргумент | Описание |
| --- | --- |
| `$mimeTypes` | Конкретный MIME-тип; параметры не участвуют в поиске. |

**Возвращает:** Тот же экземпляр карты.

**Исключения:** `InvalidMimeTypeException`.


#### `setMap()`

`setMap(array $map): self`

Атомарно заменяет весь справочник.

| Аргумент | Описание |
| --- | --- |
| `$map` | Соответствия MIME-типов спискам расширений (`array<string, list<string>>`). |

**Возвращает:** Тот же экземпляр карты.

**Исключения:** `InvalidArgumentException`.


#### `getDefaultMap()`

`getDefaultMap(): array`

Статически возвращает стандартные соответствия.

**Возвращает:** `array<string, list<string>>`.


## Исключения

Исключения библиотеки находятся в `Componenta\Detector\Exception`. Категории независимы.

| Контракт | Назначение |
| --- | --- |
| `DetectorExceptionInterface` | Ошибки определения типа или разрешения расширения и конфигурации. |
| `FileExceptionInterface` | Ошибка получения файлового источника. |
| `StreamExceptionInterface` | Ошибка чтения или позиционирования потока. |
| `StreamPositionRestoreExceptionInterface` | Ошибка восстановления позиции; уточняет потоковый контракт. |

| Исключение | Причина |
| --- | --- |
| `DetectorException` | Не удалось определить тип или разрешить расширение. |
| `DetectorConfigurationException` | Недопустимая конфигурация; наследует InvalidArgumentException. |
| `FileUnavailableException` | Файл отсутствует или недоступен при разрешении пути. |
| `InvalidFileException` | Недопустимый путь или тип файла. |
| `FileReadException` | Ошибка открытия, чтения, проверки открытого файла или закрытия. |
| `StreamReadException` | Ошибка чтения или первоначального позиционирования потока. |
| `StreamPositionRestoreException` | Исходную позицию восстановить не удалось. |
| `InvalidMimeTypeException` | Недопустимый MIME-тип; наследует InvalidArgumentException. |

В случае сбоя восстановления позиции `getPrevious()` содержит причину восстановления, а `readFailure` — исходную ошибку, если она была. Если закрытие файла тоже завершается ошибкой, `FileReadException::$readFailure` сохраняет предшествующий сбой. Ошибки программирования не заменяются обычным отрицательным результатом. Неожиданные сбои проверки состояния потока передаются без изменения, если его позиция успешно восстановлена.

```php
use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\FileExceptionInterface;

try {
    $mime = $detector->detectFileMimeType($path);
} catch (FileExceptionInterface $error) {
    // Файловый источник недоступен.
} catch (DetectorExceptionInterface $error) {
    // Не удалось определить тип.
}
```

## Перечисления MIME-типов

Перечисления в `Componenta\Detector\MimeTypes` содержат канонические типы в нижнем регистре и метод `values()`. Специализированные выборки, например `Image::commonWebFormats()`, не являются проверкой безопасности или гарантией поддержки браузером. Справочник является локальным, а не полным реестром IANA.


### Общие методы перечислений

Все девять перечислений поддерживают следующие методы.

#### `values()`

`values(): array`

Возвращает список строковых значений всех канонических элементов в порядке объявления, без повторов и псевдонимов (`list<string>`). Аргументов нет.

#### `from()`

`from(int|string $value): static`

Стандартный метод строкового перечисления PHP. Возвращает элемент с точным строковым значением. `$value` — каноническая строка MIME-типа в нижнем регистре, без параметров. При отсутствии элемента выбрасывает `ValueError`; в строгом режиме PHP необходимо передавать строку, иначе выбрасывается `TypeError`.

#### `tryFrom()`

`tryFrom(int|string $value): ?static`

Принимает те же значения, что и `from()`, но возвращает `null`, если соответствующий элемент отсутствует; в строгом режиме также требуется строка.

#### `cases()`

`cases(): array`

Возвращает все канонические элементы перечисления в порядке объявления (`list<self>`). Аргументов нет.

### Классификация по категории

Каждый из приведённых методов — статический. Аргумент `$mimeType` — строка конкретного MIME-типа с необязательными параметрами. Регистр имени типа не учитывается. Недопустимый тип, шаблон или другая категория дают `false`; принадлежность к категории не требует наличия элемента в перечислении.

| Перечисление | Сигнатура | Условие результата `true` |
| --- | --- | --- |
| [Application](src/MimeTypes/Application.php) | `isApplication(string $mimeType): bool` | Тип категории `application`. |
| [Audio](src/MimeTypes/Audio.php) | `isAudio(string $mimeType): bool` | Тип категории `audio`. |
| [Font](src/MimeTypes/Font.php) | `isFont(string $mimeType): bool` | Тип `font/*` или известный устаревший тип шрифта. |
| [Image](src/MimeTypes/Image.php) | `isImage(string $mimeType): bool` | Тип категории `image`. |
| [Message](src/MimeTypes/Message.php) | `isMessage(string $mimeType): bool` | Тип категории `message`. |
| [Model](src/MimeTypes/Model.php) | `isModel(string $mimeType): bool` | Тип категории `model`. |
| [Multipart](src/MimeTypes/Multipart.php) | `isMultipart(string $mimeType): bool` | Тип категории `multipart`. |
| [Text](src/MimeTypes/Text.php) | `isText(string $mimeType): bool` | Тип категории `text`. |
| [Video](src/MimeTypes/Video.php) | `isVideo(string $mimeType): bool` | Тип категории `video`. |

### Тематические выборки

Все методы ниже статические, не принимают аргументов и возвращают упорядоченный список элементов соответствующего перечисления (`list<self>`). Списки подобраны вручную, не являются исчерпывающими и не предназначены для проверки безопасности, качества или совместимости форматов.

#### [`Application`](src/MimeTypes/Application.php)

| Сигнатура | Назначение |
| --- | --- |
| `archives(): array` | Распространённые типы архивов. |
| `documents(): array` | Распространённые типы документов, электронных таблиц и презентаций. |
| `data(): array` | Избранные типы сериализации и обмена данными. |

#### [`Audio`](src/MimeTypes/Audio.php)

| Сигнатура | Назначение |
| --- | --- |
| `common(): array` | Распространённые типы аудио. |
| `lossless(): array` | Типы форматов, часто используемых для аудио без потерь; MIME-тип сам по себе не гарантирует кодирование без потерь. |
| `commonWebFormats(): array` | Распространённые форматы аудио для веб-приложений. |

#### [`Font`](src/MimeTypes/Font.php)

| Сигнатура | Назначение |
| --- | --- |
| `commonWebFormats(): array` | Распространённые форматы шрифтов для веб-приложений. |
| `modern(): array` | WOFF2 и WOFF в указанном порядке. |

#### [`Image`](src/MimeTypes/Image.php)

| Сигнатура | Назначение |
| --- | --- |
| `common(): array` | Распространённые типы изображений. |
| `commonWebFormats(): array` | Распространённые форматы изображений для веб-приложений. |

#### [`Message`](src/MimeTypes/Message.php)

| Сигнатура | Назначение |
| --- | --- |
| `email(): array` | Избранные типы электронной почты и уведомлений. |

#### [`Model`](src/MimeTypes/Model.php)

| Сигнатура | Назначение |
| --- | --- |
| `common(): array` | Распространённые форматы трёхмерных моделей. |
| `commonWebFormats(): array` | Избранные форматы для веб-приложений с трёхмерной графикой. |
| `printing3d(): array` | Избранные типы, используемые при трёхмерной печати. |

#### [`Multipart`](src/MimeTypes/Multipart.php)

| Сигнатура | Назначение |
| --- | --- |
| `common(): array` | Распространённые типы составных сообщений. |

#### [`Text`](src/MimeTypes/Text.php)

| Сигнатура | Назначение |
| --- | --- |
| `programming(): array` | Избранные типы исходного кода языков программирования. |
| `web(): array` | Избранные текстовые типы для веб-разработки. |
| `data(): array` | Избранные текстовые форматы данных. |
| `markup(): array` | Избранные типы языков разметки. |

#### [`Video`](src/MimeTypes/Video.php)

| Сигнатура | Назначение |
| --- | --- |
| `common(): array` | Распространённые типы видео. |
| `commonWebFormats(): array` | Распространённые форматы видео для веб-приложений. |
| `streaming(): array` | Избранные типы для потоковой передачи. |
| `highQuality(): array` | Избранные типы, связанные с видео высокого качества; MIME-тип не определяет фактическое качество. |


## [`ExtensionResolverInterface`](src/ExtensionResolverInterface.php)

Узкий контракт поиска предпочтительного расширения, не требующий реализации полного справочника. `MimeMapInterface` наследует этот контракт.

#### `getExtension()`

`getExtension(string $mimeType): ?string`

Определяет нормализованное предпочтительное расширение без ведущей точки. Параметры конкретного MIME-типа не учитываются.

| Аргумент | Описание |
| --- | --- |
| `$mimeType` | Конкретный MIME-тип для поиска. |

**Возвращает:** расширение или `null`, если тип недопустим либо соответствие отсутствует.

**Исключения:** `DetectorExceptionInterface` при штатном сбое внешнего источника соответствий. Неожиданные ошибки передаются без изменения.

## Внедрение зависимостей

`ConfigProvider` регистрирует `MimeMap` как стандартный `MimeMapInterface` и `FinfoDetector` через фабрику. Все четыре контракта и `DetectorInterface` разрешаются в один сервис согласно правилам жизненного цикла контейнера. Стандартная привязка `ExtensionResolverInterface` указывает на `MimeMapInterface`, поэтому переопределение карты по-прежнему влияет на детектор. Если нужен только поиск предпочтительного расширения, переопределите `ExtensionResolverInterface` напрямую. Пользовательский источник соответствий должен сообщать о штатных сбоях поиска через `DetectorExceptionInterface`; прочие исключения передаются без изменения.

```php
use Componenta\Detector\MimeTypeDetectorInterface;

$detector = $container->get(MimeTypeDetectorInterface::class);
```

## Безопасность

MIME-тип и расширение не подтверждают безопасность, подлинность или полную корректность файла. Независимо проверяйте содержимое, используйте разрешённые форматы, ограничения размера, корректные заголовки ответа, изоляцию и политику хранения. HTML, JavaScript, SVG и другие форматы могут содержать активное содержимое.
