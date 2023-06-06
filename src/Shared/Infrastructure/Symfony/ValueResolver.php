<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Symfony;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use TaskManager\Shared\Infrastructure\Service\ContentDecoderInterface;

abstract class ValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly ContentDecoderInterface $decoder)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (!$argumentType  || !is_a($argumentType, $this->supportClass(), true)) {
            return [];
        }

        $attributes = $this->decoder->decode($request->getContent());

        return $this->doResolve($attributes);
    }

    abstract protected function supportClass(): string;

    abstract protected function doResolve(array $attributes): iterable;
}
