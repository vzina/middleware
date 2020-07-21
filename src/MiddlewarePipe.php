<?php
/**
 * MiddlewarePipe.php
 * PHP version 7
 *
 * @category middleware
 * @package  EyPhp\Middleware
 * @author   Weijian.Ye <yeweijian@3k.com>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/vzina
 */
declare(strict_types=1);

namespace EyPhp\Middleware;


use EyPhp\Middleware\Exception\MiddlewareException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

class MiddlewarePipe implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var SplQueue
     * @author Weijian.Ye <yeweijian@3k.com>
     */
    protected $pipeline;

    public function __construct(SplQueue $pipeline = null)
    {
        $this->pipeline = $pipeline ?: new SplQueue();
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        return $this->handle($request);
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $next = new Next($this->pipeline, $handler);

        return $next($request);
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws MiddlewareException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->pipeline->isEmpty()) {
            throw new MiddlewareException('empty middleware pipeline.');
        }

        $pipeline = clone $this;
        $middleware = $pipeline->pipeline->dequeue();

        return $middleware->process($request, $pipeline);
    }

    /**
     * Attach middleware to the pipeline.
     *
     * @param MiddlewareInterface|callable ...$middleware
     */
    public function add(MiddlewareInterface ...$middleware)
    {
        foreach ($middleware as $item) {
            $this->pipeline->enqueue($item);
        }
    }
}