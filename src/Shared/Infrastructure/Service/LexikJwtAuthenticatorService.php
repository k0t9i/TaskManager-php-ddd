<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TaskManager\Shared\Application\Service\AuthenticatorServiceInterface;
use TaskManager\Shared\Domain\Exception\AuthenticationException;
use TaskManager\Shared\Infrastructure\ValueObject\SymfonyUser;

final class LexikJwtAuthenticatorService implements AuthenticatorServiceInterface, EventSubscriberInterface
{
    private ?string $userId = null;
    private string $pathRegexp;

    public function __construct(
        private readonly JWTTokenManagerInterface $tokenManager,
        private readonly TokenExtractorInterface $tokenExtractor,
        private $path
    ) {
        $this->configurePathRegexp();
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getToken(string $id): string
    {
        return $this->tokenManager->create(new SymfonyUser($id));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(KernelEvent $event): void
    {
        $uri = $event->getRequest()->getRequestUri();

        try {
            $payload = $this->extractTokenPayloadFromRequest($event->getRequest());

            $this->userId = $this->getUserIdClaim($payload);
        } catch (AuthenticationException $e) {
            if (preg_match($this->pathRegexp, $uri) > 0) {
                throw $e;
            }
        }
    }

    private function extractTokenPayloadFromRequest(Request $request): array
    {
        $token = $this->tokenExtractor->extract($request);
        $token = false === $token ? '' : $token;

        try {
            $payload = $this->tokenManager->parse($token);
            if (!$payload) {
                throw new AuthenticationException('Invalid JWT Token');
            }

            return $payload;
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new AuthenticationException('Expired token');
            }

            throw new AuthenticationException('Invalid JWT Token');
        }
    }

    private function getUserIdClaim(array $payload): ?string
    {
        $idClaim = $this->tokenManager->getUserIdClaim();
        if (!isset($payload[$idClaim])) {
            throw new AuthenticationException(sprintf('Invalid payload "%s"', $idClaim));
        }

        return $payload[$idClaim];
    }

    private function configurePathRegexp(): void
    {
        $this->pathRegexp = '/'.str_replace('/', '\/', $this->path).'/';
        if (false === @preg_match($this->pathRegexp, '')) {
            throw new \LogicException(sprintf('Invalid path regexp "%s"', $this->path));
        }
    }
}
