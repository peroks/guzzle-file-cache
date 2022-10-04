# Simple file caching for the Guzzle http client
### A lightweight file cache for Guzzle 7+ implementing the PSR-16 Caching Interface

## How to use

    <?php
    
    use GuzzleHttp\Client;
    use GuzzleHttp\HandlerStack;
    use Peroks\GuzzleFileCache\Cache;
    use Peroks\GuzzleFileCache\FileStorage;
    
    function getGuzzleClient( array $options = [] ): Client {
        $stack = HandlerStack::create();
        $store = new FileStorage( 'your/cache/directory' );
        $cache = new Cache( $store );
    
        $stack->push( $cache );
        $options['handler'] = $stack;
    
        return new Client( $options );
    }

You use an instance of the `Peroks\GuzzleFileCache\Cache` class to add
file-caching as a handler (middleware) to the Guzzle http client.

`Peroks\GuzzleFileCache\FileStorage` does the real work, implementing the
[PSR-16: Common Interface for Caching Libraries](https://www.php-fig.org/psr/psr-16/).

After you have added caching to the Guzzle http client, there is a new
option for caching: `ttl` (time-to-live). The `ttl` value is in seconds.

You can set this option for each request sent through the client.
If you omit this option or set `ttl` to `0`, the request is not cached.

    $response = $client->send( $request, [ 'ttl' => 3600 ] ); // 1 hour caching.

## Installing

You need **composer** to download and install peroks/guzzle-file-cache.
Just run `composer require peroks/guzzle-file-cache` in your project.
