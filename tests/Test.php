<?php

declare(strict_types=1);

namespace Dbp\Relay\CoreConnectorTextfileBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class Test extends ApiTestCase
{
    public function testBasics()
    {
        $client = static::createClient();
        $this->assertNotNull($client);
    }
}
