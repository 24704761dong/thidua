<?php

/**
 * Check whether current logged-in account can manage school weeks
 * (create, edit, delete, lock/unlock).
 */
function can_current_user_manage_weeks(): bool
{
    $role = $_SESSION['user_vai_tro'] ?? '';
    $permissions = $_SESSION['user_permissions'] ?? [];

    if ($role === 'admin') {
        return true;
    }

    if ($role !== 'user' || !is_array($permissions)) {
        return false;
    }

    return in_array('all', $permissions, true) || in_array('chinh_sua_tuan_hoc', $permissions, true);
}
