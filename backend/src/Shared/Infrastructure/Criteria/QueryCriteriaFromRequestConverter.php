<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use TaskManager\Shared\Application\Criteria\QueryCriteriaDTO;
use TaskManager\Shared\Application\Criteria\QueryCriteriaFilterDTO;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final class QueryCriteriaFromRequestConverter implements QueryCriteriaFromRequestConverterInterface
{
    public const DEFAULT_OPERATOR = OperatorEnum::Equal->value;

    public function convert(RequestCriteriaDTO $dto): QueryCriteriaDTO
    {
        $filters = [];
        foreach ($dto->filters as $filterMetadata => $value) {
            $parts = explode(':', $filterMetadata);

            $operator = self::DEFAULT_OPERATOR;
            $property = $parts[0];
            if (count($parts) > 1) {
                $operator = mb_strtolower($parts[1]);
            }
            $filters[] = new QueryCriteriaFilterDTO($property, $operator, $value);
        }

        $orders = [];
        foreach ($dto->orders as $orderMetadata) {
            $first = $orderMetadata[0];
            $isAsc = '-' === $first;
            $property = ltrim($orderMetadata, '-+');
            $orders[$property] = $isAsc;
        }

        $page = $dto->page ?? 1;

        return new QueryCriteriaDTO(
            $filters,
            $orders,
            ($page - 1) * Pagination::PAGE_SIZE,
            Pagination::PAGE_SIZE
        );
    }
}
