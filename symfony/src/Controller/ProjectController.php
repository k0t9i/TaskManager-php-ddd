<?php

declare(strict_types=1);

namespace SymfonyApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use TaskManager\Projects\Application\Command\ActivateProjectCommand;
use TaskManager\Projects\Application\Command\ChangeProjectInformationCommand;
use TaskManager\Projects\Application\Command\ChangeProjectOwnerCommand;
use TaskManager\Projects\Application\Command\CloseProjectCommand;
use TaskManager\Projects\Application\Command\ConfirmRequestCommand;
use TaskManager\Projects\Application\Command\CreateProjectCommand;
use TaskManager\Projects\Application\Command\CreateRequestCommand;
use TaskManager\Projects\Application\Command\CreateTaskCommand;
use TaskManager\Projects\Application\Command\LeaveCommand;
use TaskManager\Projects\Application\Command\RejectRequestCommand;
use TaskManager\Projects\Application\Command\RemoveParticipantCommand;
use TaskManager\Projects\Infrastructure\Service\DTO\ProjectInformationDTO;
use TaskManager\Projects\Infrastructure\Service\DTO\TaskInformationDTO;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;

#[AsController]
#[Route('/api/projects', name: 'project.')]
final readonly class ProjectController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UuidGeneratorInterface $uuidGenerator
    ) {
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(ProjectInformationDTO $dto): JsonResponse
    {
        $command = new CreateProjectCommand(
            $this->uuidGenerator->generate(),
            $dto->name,
            $dto->description,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }

    #[Route('/{id}/', name: 'update', methods: ['PATCH'])]
    public function update(string $id, ProjectInformationDTO $dto): JsonResponse
    {
        $command = new ChangeProjectInformationCommand(
            $id,
            $dto->name,
            $dto->description,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/activate/', name: 'activate', methods: ['PATCH'])]
    public function activate(string $id): JsonResponse
    {
        $command = new ActivateProjectCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/close/', name: 'close', methods: ['PATCH'])]
    public function close(string $id): JsonResponse
    {
        $command = new CloseProjectCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/change-owner/{ownerId}/', name: 'changeOwner', methods: ['PATCH'])]
    public function changeOwner(string $id, string $ownerId): JsonResponse
    {
        $command = new ChangeProjectOwnerCommand($id, $ownerId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/remove-participant/{participantId}/', name: 'removeParticipant', methods: ['PATCH'])]
    public function removeParticipant(string $id, string $participantId): JsonResponse
    {
        $command = new RemoveParticipantCommand($id, $participantId);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/leave/', name: 'leave', methods: ['PATCH'])]
    public function leave(string $id): JsonResponse
    {
        $command = new LeaveCommand($id);

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/requests/', name: 'createRequest', methods: ['POST'])]
    public function createRequest(string $id): JsonResponse
    {
        $command = new CreateRequestCommand(
            $this->uuidGenerator->generate(),
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }

    #[Route('/{id}/requests/{requestId}/confirm/', name: 'confirmRequest', methods: ['PATCH'])]
    public function confirmRequest(string $id, string $requestId): JsonResponse
    {
        $command = new ConfirmRequestCommand(
            $requestId,
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/requests/{requestId}/reject/', name: 'rejectRequest', methods: ['PATCH'])]
    public function rejectRequest(string $id, string $requestId): JsonResponse
    {
        $command = new RejectRequestCommand(
            $requestId,
            $id
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse();
    }

    #[Route('/{id}/tasks/', name: 'createTask', methods: ['POST'])]
    public function createTask(string $id, TaskInformationDTO $dto): JsonResponse
    {
        $command = new CreateTaskCommand(
            $this->uuidGenerator->generate(),
            $id,
            $dto->name,
            $dto->brief,
            $dto->description,
            $dto->startDate,
            $dto->finishDate
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => $command->id], Response::HTTP_CREATED);
    }
}
