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
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/name'
            )]
        )]
        #[Groups(['create', 'update'])]
        #[Assert\NotBlank(groups: ['create'])]
        public string $name = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/brief'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $brief = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/description'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $description = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/startDate'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $startDate = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/taskModel/properties/finishDate'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $finishDate = ''
    ) {
    }
}
