<?php

declare(strict_types=1);

namespace Componenta\Detector\Exception;

interface StreamPositionRestoreExceptionInterface extends StreamExceptionInterface
{
    /** An earlier read failure, if restoration failed while handling it. */
    public ?\Throwable $readFailure { get; }
}
