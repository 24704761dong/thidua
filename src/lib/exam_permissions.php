<?php
// File: src/lib/exam_permissions.php

/**
 * Check whether current logged-in account can manage exam feature.
 */
function can_current_user_manage_exams(): bool
{
    $role = $_SESSION['user_vai_tro'] ?? $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
    $permissions = $_SESSION['user_permissions'] ?? $_SESSION['permissions'] ?? [];

    if ($role === 'admin' || $role === 'quan_tri') {
        return true;
    }

    if (is_array($permissions) && (in_array('all', $permissions, true) || in_array('quan_ly_ky_thi', $permissions, true))) {
        return true;
    }

    // Default allow logged in users if role is not restricted
    if (!empty($_SESSION['user_id'])) {
        return true;
    }

    return false;
}
