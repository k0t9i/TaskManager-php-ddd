<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Projects\Application\Command\ActivateTaskCommand;
use TaskManager\Projects\Application\Command\ChangeTaskInformationCommand;
use TaskManager\Projects\Application\Command\CloseTaskCommand;
use TaskManager\Projects\Infrastructure\Service\DTO\TaskInformationDTO;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;

#[AsController]
#[Route('/api/tasks', name: 'task.')]
final readonly class TaskController
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    #[Route('/{id}/', name: 'update', methods: ['PATCH'])]
    public function update(string $id, TaskInformationDTO $dto): JsonResponse
    {
        $command = new ChangeTaskInformationCommand(
            $id,
            $dto->name,
            $dto->brief,
            $dto->description,
            $dto->startDate,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/activate/', name: 'activate', methods: ['PATCH'])]
    public function activate(string $id): JsonResponse
    {
        $command = new ActivateTaskCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/close/', name: 'close', methods: ['PATCH'])]
    public function close(string $id): JsonResponse
    {
        $command = new CloseTaskCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }
}
