<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Projects\Application\Command\ActivateTaskCommand;
use TaskManager\Projects\Application\Command\ChangeTaskInformationCommand;
use TaskManager\Projects\Application\Command\CloseTaskCommand;
use TaskManager\Projects\Application\Command\CreateTaskLinkCommand;
use TaskManager\Projects\Application\Command\DeleteTaskLinkCommand;
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

    #[Route('/{id}/links/{linkedTaskId}/', name: 'createLink', methods: ['POST'])]
    public function createLink(string $id, string $linkedTaskId): JsonResponse
    {
        $command = new CreateTaskLinkCommand($id, $linkedTaskId);

        $this->commandBus->dispatch($command);

        return new JsonResponse([], Response::HTTP_CREATED);
    }

    #[Route('/{id}/links/{linkedTaskId}/', name: 'deleteLink', methods: ['DELETE'])]
    public function deleteLink(string $id, string $linkedTaskId): JsonResponse
    {
        $command = new DeleteTaskLinkCommand($id, $linkedTaskId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }
}
