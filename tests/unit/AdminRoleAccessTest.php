<?php

use App\Controllers\AdminUsers;
use App\Models\AdminUserModel;
use CodeIgniter\Test\CIUnitTestCase;

final class AdminRoleAccessTest extends CIUnitTestCase
{
    public function testRoleOptionsExposeSuperAdminAndOperator(): void
    {
        $this->assertSame(['super_admin', 'operator'], AdminUserModel::getRoleOptions());
    }

    public function testSuperAdminAccessCheckBlocksOperatorAndUnknownRoles(): void
    {
        $this->assertTrue(AdminUsers::isAllowedAccess('super_admin'));
        $this->assertFalse(AdminUsers::isAllowedAccess('operator'));
        $this->assertFalse(AdminUsers::isAllowedAccess('guest'));
    }
}
