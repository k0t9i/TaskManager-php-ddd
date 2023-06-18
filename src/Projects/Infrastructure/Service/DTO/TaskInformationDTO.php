<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Service\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TaskInformationDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Task name',
            type: 'string',
            maxLength: 255,
            example: 'My task'
        )]
        #[Groups(['create', 'update'])]
        #[Assert\NotBlank(groups: ['create'])]
        public string $name = '',
        #[OA\Property(
            description: 'Task brief info',
            type: 'string',
            maxLength: 2000,
            example: 'Yet another awesome task.'
        )]
        #[Groups(['create', 'update'])]
        public string $brief = '',
        #[OA\Property(
            description: 'Task description',
            type: 'string',
            maxLength: 4000,
            example: 'Yet another awesome task.'
        )]
        #[Groups(['create', 'update'])]
        public string $description = '',
        #[OA\Property(
            description: 'Task start date',
            type: 'string',
            format: 'date',
            example: '2023-05-01'
        )]
        #[Groups(['create', 'update'])]
        public string $startDate = '',
        #[OA\Property(
            description: 'Task finish date',
            type: 'string',
            format: 'date',
            example: '2023-05-07'
        )]
        #[Groups(['create', 'update'])]
        public string $finishDate = ''
    ) {
    }
}
