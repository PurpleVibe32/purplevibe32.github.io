<?php

namespace App\Middlewares;

use App\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PruneCacheMiddleware
{
    /** @var Config The application configuration */
    protected $config;

    /** @var CacheInterface The application cache */
    protected $cache;

    /** Create a new CachePruneMiddleware object. */
    public function __construct(Config $config, CacheInterface $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /** Invoke the CachePruneMiddleware class. */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (! $this->cache instanceof PruneableInterface) {
            return $response;
        }

        if ($this->winsLottery()) {
            $this->cache->prune();
        }

        return $response;
    }

    /** Determine if this request wins the lottery. */
    private function winsLottery(): bool
    {
        return random_int(1, 100) <= $this->config->get('cache_lottery');
    }
}
