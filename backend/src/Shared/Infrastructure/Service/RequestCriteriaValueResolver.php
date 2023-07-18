<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use TaskManager\Shared\Infrastructure\Service\DTO\RequestCriteriaDTO;

final class RequestCriteriaValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (!$argumentType || !is_a($argumentType, RequestCriteriaDTO::class, true)) {
            return [];
        }

        $filters = $request->query->all('filter');
        $orders = $request->query->all('order');

        yield new RequestCriteriaDTO($filters, $orders);
    }
}
