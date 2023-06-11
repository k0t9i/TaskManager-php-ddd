<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TaskManager\Shared\Infrastructure\Service\ArrayArgumentLoaderCompilerPass;

final class ArrayArgumentLoaderCompilerPassTest extends TestCase
{
    public function testProcessWithValidConfiguration(): void
    {
        $targetTag1 = 'test_target_tag';
        $targetTag2 = 'test_target_tag2';
        $targets1 = [
            'targetOne', 'target2', 'lastTarget',
        ];
        $targets2 = [
            'anotherTarget',
        ];
        $container = new ContainerBuilder();
        $container->register('test')
            ->addTag(ArrayArgumentLoaderCompilerPass::TAG, [
                ArrayArgumentLoaderCompilerPass::TARGET_TAG_FIELD => $targetTag1,
            ]);
        $container->register('same_target_tag')
            ->addTag(ArrayArgumentLoaderCompilerPass::TAG, [
                ArrayArgumentLoaderCompilerPass::TARGET_TAG_FIELD => $targetTag1,
            ]);
        $container->register('different_target_tag')
            ->addTag(ArrayArgumentLoaderCompilerPass::TAG, [
                ArrayArgumentLoaderCompilerPass::TARGET_TAG_FIELD => $targetTag2,
            ]);
        $container->register('empty')
            ->addTag(ArrayArgumentLoaderCompilerPass::TAG, [
                ArrayArgumentLoaderCompilerPass::TARGET_TAG_FIELD => 'foo',
            ]);
        $container->register('random_service');

        foreach ($targets1 as $target) {
            $container->register($target)
                ->addTag($targetTag1);
        }
        foreach ($targets2 as $target) {
            $container->register($target)
                ->addTag($targetTag2);
        }

        (new ArrayArgumentLoaderCompilerPass())->process($container);

        $this->assertEquals($targets1, $container->getDefinition('test')->getArguments()[0]);
        $this->assertEquals($targets1, $container->getDefinition('same_target_tag')->getArguments()[0]);
        $this->assertEquals($targets2, $container->getDefinition('different_target_tag')->getArguments()[0]);
        $this->assertEmpty($container->getDefinition('empty')->getArguments()[0]);
        $this->assertEmpty($container->getDefinition('random_service')->getArguments());
    }

    public function testProcessWithInvalidConfiguration(): void
    {
        $container = new ContainerBuilder();
        $container->register('test')
            ->addTag(ArrayArgumentLoaderCompilerPass::TAG);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'The tag "%s" must have %s field.',
            ArrayArgumentLoaderCompilerPass::TAG,
            ArrayArgumentLoaderCompilerPass::TARGET_TAG_FIELD
        ));

        (new ArrayArgumentLoaderCompilerPass())->process($container);
    }
}
