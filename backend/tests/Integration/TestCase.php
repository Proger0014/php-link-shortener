<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\Attributes\Before;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class TestCase extends KernelTestCase
{
    private ?KernelInterface $app = null;

    #[Before]
    public function boot()
    {
        if (is_null($this->app)) {
            $this->app = self::bootKernel();
        }
    }
}
