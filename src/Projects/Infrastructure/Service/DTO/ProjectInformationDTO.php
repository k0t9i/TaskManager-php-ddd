<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Service\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProjectInformationDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Project name',
            type: 'string',
            maxLength: 255,
            example: 'My project'
        )]
        #[Groups(['create', 'update'])]
        #[Assert\NotBlank(groups: ['create'])]
        public string $name = '',
        #[OA\Property(
            description: 'Project description',
            type: 'string',
            maxLength: 4000,
            example: 'Yet another awesome project.'
        )]
        #[Groups(['create', 'update'])]
        public string $description = '',
        #[OA\Property(
            description: 'Project finish date',
            type: 'string',
            format: 'date',
            example: '2023-05-10'
        )]
        #[Groups(['create', 'update'])]
        public string $finishDate = ''
    ) {
    }
}
