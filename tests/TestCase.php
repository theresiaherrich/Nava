<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $config = $app['config'];

        if ($config->get('database.default') !== 'sqlite' || $config->get('database.connections.sqlite.database') !== ':memory:'
            || filled($config->get('database.connections.sqlite.url'))) {
            throw new LogicException('Tes fitur memerlukan SQLite in-memory. Jalankan php artisan config:clear dan ulangi.');
        }

        if (blank($config->get('app.key'))) {
            $config->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        }

        return $app;
    }
}
