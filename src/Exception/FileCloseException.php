<?php

declare(strict_types=1);

namespace Componenta\Detector\Exception;

/** Closing the reader-owned file failed; retains any earlier failure. */
final class FileCloseException extends FileReadException {}
