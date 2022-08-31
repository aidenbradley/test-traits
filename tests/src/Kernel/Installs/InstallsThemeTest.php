<?php

namespace Drupal\Tests\drupal_test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\drupal_test_support\Traits\Installs\InstallsTheme;

class InstallsThemeTest extends KernelTestBase
{
    use InstallsTheme;

    protected $strictConfigSchema = false;

    /** @test */
    public function installs_theme(): void
    {
        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        $this->installTheme('seven');

        $this->assertArrayHasKey('seven', $this->container->get('theme_handler')->listInfo());

        $this->installTheme('bartik');

        $this->assertArrayHasKey('bartik', $this->container->get('theme_handler')->listInfo());
    }
}
