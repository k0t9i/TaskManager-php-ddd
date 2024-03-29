<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use TaskManager\Shared\Domain\Exception\AuthenticationException;
use TaskManager\Shared\Infrastructure\Service\LexikJwtAuthenticatorService;
use TaskManager\Shared\Infrastructure\ValueObject\SymfonyUser;

class LexikJwtAuthenticatorServiceTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testOnKernelController(): void
    {
        $idClaim = $this->faker->regexify('[a-zA-Z]{50}');
        $token = $this->faker->regexify('.{255}');
        $path = 'abc';
        $userId = $this->faker->uuid();
        $payload = [
            $idClaim => $userId,
        ];
        $tokenManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManager->expects(self::once())
            ->method('parse')
            ->with($token)
            ->willReturn($payload);
        $tokenManager->expects(self::once())
            ->method('getUserIdClaim')
            ->willReturn($idClaim);
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();
        $tokenExtractor->expects(self::once())
            ->method('extract')
            ->willReturn($token);
        $service = new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->expects(self::once())
            ->method('getRequestUri');
        $event = $this->getMockBuilder(KernelEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(self::atLeast(1))
            ->method('getRequest')
            ->willReturn($request);

        $service->onKernelController($event);

        $this->assertEquals($userId, $service->getUserId()->value);
    }

    public function testGetToken(): void
    {
        $path = 'abc';
        $userId = $this->faker->uuid();
        $symfonyUser = new SymfonyUser($userId);
        $token = $this->faker->regexify('.{255}');
        $tokenManager = $this->getMockBuilder(JWTTokenManagerInterface::class)
            ->getMock();
        $tokenManager->expects(self::once())
            ->method('create')
            ->with($symfonyUser)
            ->willReturn($token);
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();

        $service = new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);

        $this->assertEquals($token, $service->getToken($userId));
    }

    public function testCreateWithInvalidPath(): void
    {
        $path = $this->faker->regexify('.{255}');
        $tokenManager = $this->getMockBuilder(JWTTokenManagerInterface::class)
            ->getMock();
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Invalid path regexp "%s"', $path));

        new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);
    }

    public function testEmptyPayload(): void
    {
        $path = 'abc';
        $tokenManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManager->method('parse')
            ->willReturn([]);
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();
        $tokenExtractor->method('extract')
            ->willReturn('foobar');
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->method('getRequestUri')
            ->willReturn($path);
        $event = $this->getMockBuilder(KernelEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('getRequest')
            ->willReturn($request);

        $service = new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        $service->onKernelController($event);
    }

    public function testExpiredToken(): void
    {
        $path = 'abc';
        $tokenManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManager->method('parse')
            ->willReturn([
                'foo' => 'bar',
            ])
            ->willThrowException(new JWTDecodeFailureException(
                JWTDecodeFailureException::EXPIRED_TOKEN,
                'message'
            ));
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();
        $tokenExtractor->method('extract')
            ->willReturn('foobar');
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->method('getRequestUri')
            ->willReturn($path);
        $event = $this->getMockBuilder(KernelEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('getRequest')
            ->willReturn($request);

        $service = new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Expired token');

        $service->onKernelController($event);
    }

    public function testOtherTokenParseException(): void
    {
        $path = 'abc';
        $tokenManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenManager->method('parse')
            ->willReturn([
                'foo' => 'bar',
            ])
            ->willThrowException(new JWTDecodeFailureException(
                'foobar',
                'message'
            ));
        $tokenExtractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->getMock();
        $tokenExtractor->method('extract')
            ->willReturn('foobar');
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->method('getRequestUri')
            ->willReturn($path);
        $event = $this->getMockBuilder(KernelEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('getRequest')
            ->willReturn($request);

        $service = new LexikJwtAuthenticatorService($tokenManager, $tokenExtractor, $path);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        $service->onKernelController($event);
    }
}
