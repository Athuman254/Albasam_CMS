<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->truncateLaratrustTables();

        $allModules = Config::get('permissions_seeder.modules');

        $mapPermission = collect(config('permissions_seeder.permissions_map'));

        $permissions = [];

        foreach ($allModules as $key => $moduleConfig) {
            foreach ($moduleConfig as $module => $permissionConfig) {
                foreach (explode(',', $permissionConfig) as $perm) {
                    $permissionValue = $mapPermission->get($perm);

                    $permissions[] = Permission::firstOrCreate([
                        'name'         => $permissionValue . '-' . $module,
                        'code'         => $key,
                        'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description'  => ucfirst($permissionValue) . ' ' . ucfirst($module),
                    ])->id;
                }
            }
        }

        $user = User::where('email', '=', 'admin@app.com')->first();
        $user->syncPermissionsWithoutDetaching($permissions);
    }

    /**
     * Truncates all the laratrust tables and the users table
     *
     * @return  void
     */
    public function truncateLaratrustTables(): void
    {
//        $this->command->info('Truncating User, Role and Permission tables');
        Schema::disableForeignKeyConstraints();

        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('permissions')->truncate();
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();
        DB::table('teams')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
