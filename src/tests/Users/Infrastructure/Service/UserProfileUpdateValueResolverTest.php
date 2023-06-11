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
use TaskManager\Users\Infrastructure\Service\DTO\UserProfileUpdateDTO;
use TaskManager\Users\Infrastructure\Service\UserProfileUpdateValueResolver;

class UserProfileUpdateValueResolverTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testResolveEmptyArgumentType()
    {
        $resolver = new UserProfileUpdateValueResolver(
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
        $resolver = new UserProfileUpdateValueResolver(
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
        $firstname = $this->faker->regexify('.{255}');
        $lastname = $this->faker->regexify('.{255}');
        $password = $this->faker->regexify('.{255}');
        $repeatPassword = $this->faker->regexify('.{255}');
        $attributes = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'password' => $password,
            'repeatPassword' => $repeatPassword,
        ];
        $dto = new UserProfileUpdateDTO(
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
        $resolver = new UserProfileUpdateValueResolver(
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
            ->willReturn(UserProfileUpdateDTO::class);

        /** @var PhpGenerator $result */
        $result = $resolver->resolve(
            $request,
            $metadata
        );

        $this->assertEquals($dto, $result->current());
    }
}
