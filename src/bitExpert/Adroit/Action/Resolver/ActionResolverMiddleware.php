<?php

/**
 * This file is part of the Adroit package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Adroit\Action\Resolver;

use bitExpert\Adroit\Action\ActionMiddleware;
use bitExpert\Adroit\Domain\Payload;
use bitExpert\Adroit\Resolver\AbstractResolverMiddleware;
use bitExpert\Adroit\Resolver\ResolveException;
use bitExpert\Adroit\Action\ActionExecutionException;
use bitExpert\Adroit\Resolver\Resolver;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use bitExpert\Adroit\Action\Resolver\ActionResolver;

class ActionResolverMiddleware extends AbstractResolverMiddleware implements ActionMiddleware
{
    /**
     * @var string
     */
    protected $routingResultAttribute;
    /**
     * @var string
     */
    protected $domainPayloadAttribute;

    /**
     * @param ActionResolver|ActionResolver[] $resolvers
     * @param $routingResultAttribute
     * @param $domainPayloadAttribute
     * @throws \InvalidArgumentException
     */
    public function __construct($resolvers, $routingResultAttribute, $domainPayloadAttribute)
    {
        parent::__construct($resolvers);

        $this->routingResultAttribute = $routingResultAttribute;
        $this->domainPayloadAttribute = $domainPayloadAttribute;
    }

    /**
     * @inheritdoc
     * @throws ActionResolveException
     * @throws ActionExecutionException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        try {
            /* @var $action callable */
            $action = $this->resolve($request);
        } catch (ResolveException $e) {
            throw new ActionResolveException('None of given resolvers could resolve an action', $e->getCode(), $e);
        }


        // execute the action
        $responseOrPayload = $action($request, $response);
        
        if (!($responseOrPayload instanceof Payload) && !($responseOrPayload instanceof ResponseInterface)) {
            throw new ActionExecutionException(sprintf(
                'The action "%s" did neither return an instance of "%s" nor an instance of "%s"',
                $this->getRepresentation($action),
                Payload::class,
                ResponseInterface::class
            ));
        }

        if ($next) {
            $response = $next($request->withAttribute($this->domainPayloadAttribute, $responseOrPayload), $response);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function getDomainPayloadAttribute()
    {
        return $this->domainPayloadAttribute;
    }

    /**
     * @inheritdoc
     */
    protected function isValidResolver(Resolver $resolver)
    {
        return ($resolver instanceof ActionResolver);
    }

    /**
     * @inheritdoc
     */
    protected function getIdentifier(ServerRequestInterface $request)
    {
        return $request->getAttribute($this->routingResultAttribute);
    }
}
