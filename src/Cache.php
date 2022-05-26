<?php namespace Peroks\GuzzleFileCache;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * A very simple Guzzle caching middleware.
 *
 * Responses are cached on disk by a PSR-16 caching implementation. You can also
 * create your own PSR-16 caching implementation for storing responses.
 *
 * @see https://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
 * @see https://www.php-fig.org/psr/psr-16/
 *
 * @copyright Per Egil Roksvaag
 * @license MIT License
 * @version 0.1.0
 */
class Cache {

	/**
	 * @var CacheInterface A PSR-16 caching implementation for storing responses.
	 */
	protected CacheInterface $storage;

	/**
	 * @var object An object of configuration options.
	 */
	protected object $options;

	/**
	 * Constructor.
	 *
	 * @param CacheInterface $storage A PSR-16 caching implementation for storing responses.
	 * @param array|object $options An array or object of configuration options (key/value pairs).
	 */
	public function __construct( CacheInterface $storage, $options = [] ) {
		$this->storage = $storage;
		$this->options = (object) $options;
	}

	/**
	 * Called by Guzzle's handler stack.
	 *
	 * @param callable $next The next handler to invoke.
	 *
	 * @return callable A function accepting a RequestInterface instance.
	 */
	public function __invoke( callable $next ) {
		return function( RequestInterface $request, array $options ) use ( $next ) {
			$ttl = $options['ttl'] ?? $this->options->ttl ?? 0;
			$key = $this->getKey( $request );

			/** @var ResponseInterface $response */
			if ( $ttl && $response = $this->storage->get( $key ) ) {
				if ( time() < $ttl + intval( current( $response->getHeader( 'Cached' ) ) ) ) {
					return new FulfilledPromise( $response );
				}
			}

			/** @var Promise $promise */
			$promise = $next( $request, $options );
			$promise->then( function( ResponseInterface $response ) use ( $key, $ttl ) {
				if ( $ttl && $response->getStatusCode() < 300 ) {
					$this->storage->set( $key, $response, $ttl );
				}
			} );

			return $promise;
		};
	}

	/**
	 * Generates a unique caching key for the current request.
	 *
	 * @param RequestInterface $request The current request.
	 * @param string $sep The string separator.
	 *
	 * @return string A sha1 hash cashing key.
	 */
	public function getKey( RequestInterface $request, string $sep = '|' ): string {
		$filter = $this->options->headers ?? [];
		$filter = array_fill_keys( $filter, null );

		$headers = array_intersect_key( $request->getHeaders(), $filter );
		$headers = array_map( function( $value ) use ( $sep ) {
			return join( $sep, array_filter( $value ) );
		}, $headers );

		$key = array_filter( [
			'method'  => $request->getMethod(),
			'uri'     => trim( $request->getUri() ),
			'headers' => join( $sep, $headers ),
			'body'    => trim( $request->getBody() ),
		] );

		return sha1( join( $sep, $key ) );
	}
}
