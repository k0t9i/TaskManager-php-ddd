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
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/name'
            )]
        )]
        #[Groups(['create', 'update'])]
        #[Assert\NotBlank(groups: ['create'])]
        public string $name = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/description'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $description = '',
        #[OA\Property(
            oneOf: [new OA\Schema(
                ref: '#components/schemas/projectModel/properties/finishDate'
            )]
        )]
        #[Groups(['create', 'update'])]
        public string $finishDate = ''
    ) {
    }
}
