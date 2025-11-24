<?php

use App\Models\User;
use App\Models\Role;

// Find the user
$user = User::where('email', 'xadventurebandarban@yopmail.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

// Find the role
$role = Role::where('slug', 'master-admin')->first();

if (!$role) {
    echo "Role 'master-admin' not found!\n";
    exit(1);
}

// Assign the role
$user->assignRole($role);

echo "Role 'master-admin' assigned to user '{$user->name}' ({$user->email}).\n";
