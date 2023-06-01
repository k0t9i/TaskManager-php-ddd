<?php

declare(strict_types=1);

namespace SymfonyApp;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }
}
