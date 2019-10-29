<?php

namespace Scaleplan\Youtube\Exceptions;

/**
 * Class YoutubeException
 *
 * @package Scaleplan\Youtube\Exceptions
 */
class YoutubeException extends \Exception
{
    public const MESSAGE = 'Youtube uploader error.';
    public const CODE = 500;

    /**
     * YoutubeException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: static::MESSAGE, $code ?: static::CODE, $previous);
    }
}
