<?php

declare(strict_types=1);

namespace TaskManager\Tests\Users\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Users\Domain\Entity\User;
use TaskManager\Users\Domain\ValueObject\UserEmail;
use TaskManager\Users\Domain\ValueObject\UserFirstname;
use TaskManager\Users\Domain\ValueObject\UserId;
use TaskManager\Users\Domain\ValueObject\UserLastname;
use TaskManager\Users\Domain\ValueObject\UserPassword;
use TaskManager\Users\Domain\ValueObject\UserProfile;
use TaskManager\Users\Infrastructure\Repository\DoctrineUserRepository;

class DoctrineUserRepositoryTest extends TestCase
{
    private Generator $faker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->user = new User(
            new UserId($this->faker->uuid()),
            new UserEmail($this->faker->email()),
            new UserProfile(
                new UserFirstname($this->faker->regexify('.{255}')),
                new UserLastname($this->faker->regexify('.{255}')),
                new UserPassword($this->faker->regexify('.{255}'))
            )
        );
    }

    public function testFindById(): void
    {
        $id = new UserId($this->faker->uuid());
        $repository = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();
        $repository->method('findOneBy')
            ->with([
                'id' => $id,
            ])
            ->willReturn($this->user);
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $em->method('getRepository')
            ->with(User::class)
            ->willReturn($repository);
        $object = new DoctrineUserRepository($em);

        $this->assertSame($this->user, $object->findById($id));
    }

    public function testSave(): void
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $em->method('persist')
            ->with($this->user);
        $em->expects(self::once())
            ->method('flush');
        $object = new DoctrineUserRepository($em);

        $object->save($this->user);
    }

    public function testFindByEmail(): void
    {
        $email = new UserEmail($this->faker->email());
        $repository = $this->getMockBuilder(ObjectRepository::class)
            ->getMock();
        $repository->method('findOneBy')
            ->with([
                'email' => $email,
            ])
            ->willReturn($this->user);
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $em->method('getRepository')
            ->with(User::class)
            ->willReturn($repository);
        $object = new DoctrineUserRepository($em);

        $this->assertSame($this->user, $object->findByEmail($email));
    }
}
