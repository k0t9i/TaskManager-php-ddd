<?php

declare(strict_types=1);

namespace SymfonyApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use TaskManager\Shared\Infrastructure\Service\ArrayArgumentLoaderCompilerPass;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ArrayArgumentLoaderCompilerPass());
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }
}
