<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class RemovedAdminStatisticsTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testStatisticsAdminRouteIsNoLongerAvailable(): void
    {
        try {
            $response = $this->get('/admin/statistik');
            $this->assertSame(404, $response->getStatusCode());
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
