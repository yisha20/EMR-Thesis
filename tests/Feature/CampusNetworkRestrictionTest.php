<?php

namespace Tests\Feature;

use Tests\TestCase;

class CampusNetworkRestrictionTest extends TestCase
{
    public function test_restriction_can_be_disabled_for_local_development()
    {
        config(['campus.network_restriction' => false, 'campus.allowed_ips' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->get('/')
            ->assertOk();
    }

    public function test_approved_campus_address_is_allowed()
    {
        config(['campus.network_restriction' => true, 'campus.allowed_ips' => ['10.24.0.0/16']]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.24.8.15'])
            ->get('/')
            ->assertOk();
    }

    public function test_address_outside_campus_network_is_denied()
    {
        config(['campus.network_restriction' => true, 'campus.allowed_ips' => ['10.24.0.0/16']]);

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.20'])
            ->get('/')
            ->assertForbidden();
    }

    public function test_enabled_restriction_fails_closed_when_allow_list_is_empty()
    {
        config(['campus.network_restriction' => true, 'campus.allowed_ips' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.24.8.15'])
            ->get('/')
            ->assertForbidden();
    }
}
