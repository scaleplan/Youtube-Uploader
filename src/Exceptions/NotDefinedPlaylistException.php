<?php

namespace Scaleplan\Youtube\Exceptions;

/**
 * Class NotDefinedPlaylistException
 *
 * @package Scaleplan\Youtube\Exceptions
 */
class NotDefinedPlaylistException extends YoutubeException
{
    public const MESSAGE = 'Playlist was not defined.';
    public const CODE = 400;
}
