<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use App\Models\Profile;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $admin = User::firstOrCreate(array(
            'email' => 'developer@yopmail.com',
            'name' => 'Developer'
        ));
        $admin->password = bcrypt("nmdp7788");
        $admin->save();

        if ($admin->profile == null) {
            $profile = new Profile();
            $profile->user_id = $admin->id;
            $profile->pic = 'users/no_avatar.jpg';
            $profile->save();
        }

        $user = User::firstOrCreate(array(
            'email' => 'admin@yopmail.com',
            'name' => 'Admin'
        ));
        $user->password = bcrypt("nmdp7788");
        $user->save();

        if ($user->profile == null) {
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->pic = 'users/no_avatar.jpg';
            $profile->save();
        }

        $admin_role = Role::firstOrcreate(['name' => 'developer']);
        $user_role = Role::firstOrcreate(['name' => 'admin']);

        // Get existing permissions from the PermissionTableSeeder
        // $permissions = Permission::whereIn('name', [
        //     'crud-list', 'crud-create', 'crud-edit', 'crud-delete',
        //     'user-list', 'user-create', 'user-edit', 'user-delete',
        //     'role-list', 'role-create', 'role-edit', 'role-delete'
        // ])->get();
        $permissions = Permission::pluck('id', 'id')->all();
        // Assign roles and permissions
        $admin->assignRole('developer');
        $admin_role->syncPermissions($permissions);
        $user->assignRole('admin');
        Setting::create(['title' => 'Update Title from admin', 'description' => 'Update Title from admin use for meta description tag', 'favicon' => 'AdminDashboard/default_logo.png', 'logo' => 'AdminDashboard/default-dark.svg', 'footer_text' => date('Y') . ' © Update footer form admin.']);
    }
}
