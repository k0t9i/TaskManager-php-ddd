<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use TaskManager\Shared\Domain\Exception\DomainException;
use Throwable;

final readonly class ExceptionListener
{
    public function __construct(
        private ExceptionToHttpCodeMapperInterface $codeMapper,
        private ExceptionResponseBuilderInterface $responseBuilder,
        private string $environment = 'prod'
    ) {
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $this->getParentDomainExceptionIfExists($event->getThrowable());

        $code = $this->codeMapper->getHttpCode($exception);

        $event->setResponse(
            $this->responseBuilder->build(
                $exception->getMessage(),
                $code,
                $this->environment !== 'prod' ? $exception->getTrace() : []
            )
        );
    }

    private function getParentDomainExceptionIfExists(Throwable $exception): Throwable
    {
        $result = $exception;
        while (null !== $result) {
            if ($result instanceof DomainException) {
                return $result;
            }
            $result = $result->getPrevious();
        }

        return $exception;
    }
}
