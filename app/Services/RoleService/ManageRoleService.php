<?php

namespace App\Services\RoleService;

use App\Models\Access\RolePermission;

class ManageRoleService {

	public function accessUserRole($data) {
		return RolePermission::create($data);
	}
}
