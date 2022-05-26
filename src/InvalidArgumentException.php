<?php namespace Peroks\GuzzleFileCache;

/**
 * Exception interface for invalid cache arguments.
 *
 * When an invalid argument is passed it must throw an exception which implements
 * this interface
 *
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class InvalidArgumentException extends CacheException implements \Psr\SimpleCache\InvalidArgumentException {
}
