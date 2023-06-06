<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use Generator as PhpGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use TaskManager\Shared\Infrastructure\Service\ContentDecoderInterface;
use TaskManager\Users\Infrastructure\Service\DTO\UserRegisterDTO;
use TaskManager\Users\Infrastructure\Service\UserRegisterValueResolver;

class UserRegisterValueResolverTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testResolveEmptyArgumentType()
    {
        $resolver = new UserRegisterValueResolver(
            $this->getMockBuilder(ContentDecoderInterface::class)->getMock()
        );

        $result = $resolver->resolve(
            $this->getMockBuilder(Request::class)->getMock(),
            $this->getMockBuilder(ArgumentMetadata::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->assertEquals([], $result);
    }

    public function testResolveUnsupportedArgumentType()
    {
        $resolver = new UserRegisterValueResolver(
            $this->getMockBuilder(ContentDecoderInterface::class)->getMock()
        );
        $metadata = $this->getMockBuilder(ArgumentMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->method('getType')
            ->willReturn('foobar');

        $result = $resolver->resolve(
            $this->getMockBuilder(Request::class)->getMock(),
            $metadata
        );

        $this->assertEquals([], $result);
    }

    public function testResolve()
    {
        $content = $this->faker->regexify('.{255}');
        $email = $this->faker->email();
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{255}');
        $repeatPassword = $this->faker->regexify('.{255}');
        $attributes = [
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'password' => $password,
            'repeatPassword' => $repeatPassword
        ];
        $dto = new UserRegisterDTO(
            $email,
            $firstname,
            $lastname,
            $password,
            $repeatPassword
        );
        $decoder = $this->getMockBuilder(ContentDecoderInterface::class)
            ->getMock();
        $decoder->expects(self::once())
            ->method('decode')
            ->willReturn($attributes)
            ->with($content);
        $resolver = new UserRegisterValueResolver(
            $decoder
        );
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->expects(self::once())
            ->method('getContent')
            ->willReturn($content);
        $metadata = $this->getMockBuilder(ArgumentMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(self::once())
            ->method('getType')
            ->willReturn(UserRegisterDTO::class);

        /** @var PhpGenerator $result */
        $result = $resolver->resolve(
            $request,
            $metadata
        );

        $this->assertEquals($dto, $result->current());
    }
}
