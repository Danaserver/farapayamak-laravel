<?php

namespace Farapayamak\Laravel\Tests;

use Farapayamak\Laravel\FarapayamakManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class HybridConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        FarapayamakManager::resolveConfigUsing(null);
        parent::tearDown();
    }

    public function test_it_falls_back_to_env_config(): void
    {
        $app = new Container();
        $app['config'] = new ConfigRepository([
            'farapayamak' => [
                'username' => 'env_user',
                'password' => 'env_pass',
                'from'     => '5000111',
                'database' => ['enabled' => false],
            ],
        ]);

        $manager = new FarapayamakManager($app);
        $config = $manager->getEffectiveConfig();

        $this->assertEquals('env_user', $config['username']);
        $this->assertEquals('env_pass', $config['password']);
        $this->assertEquals('5000111', $config['from']);
    }

    public function test_it_overrides_with_custom_callback_resolver(): void
    {
        FarapayamakManager::resolveConfigUsing(function () {
            return [
                'username' => 'db_user_custom',
                'password' => 'db_pass_custom',
                'from'     => '5000999',
            ];
        });

        $app = new Container();
        $app['config'] = new ConfigRepository([
            'farapayamak' => [
                'username' => 'env_user',
                'password' => 'env_pass',
                'from'     => '5000111',
            ],
        ]);

        $manager = new FarapayamakManager($app);
        $config = $manager->getEffectiveConfig();

        $this->assertEquals('db_user_custom', $config['username']);
        $this->assertEquals('db_pass_custom', $config['password']);
        $this->assertEquals('5000999', $config['from']);
    }

    public function test_it_allows_runtime_credentials_override(): void
    {
        $app = new Container();
        $app['config'] = new ConfigRepository([
            'farapayamak' => [
                'username' => 'initial_user',
                'password' => 'initial_pass',
            ],
        ]);

        $manager = new FarapayamakManager($app);
        $manager->setCredentials('runtime_user', 'runtime_pass', '5000777');

        $config = $manager->getEffectiveConfig();

        $this->assertEquals('runtime_user', $config['username']);
        $this->assertEquals('runtime_pass', $config['password']);
        $this->assertEquals('5000777', $config['from']);
    }
}
