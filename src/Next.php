<?php
/**
 * Next.php
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


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

final class Next implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $parent;

    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * Next constructor.
     * @param SplQueue                $queue
     * @param RequestHandlerInterface $parent
     */
    public function __construct(SplQueue $queue, RequestHandlerInterface $parent)
    {
        $this->queue  = clone $queue;
        $this->parent = $parent;
    }

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->handle($request);
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->queue->isEmpty()) {
            return $this->parent->handle($request);
        }

        /**
         * @var MiddlewareInterface $middleware
         */
        $middleware = $this->queue->dequeue();

        return $middleware->process($request, $this);
    }
}