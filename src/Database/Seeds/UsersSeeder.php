<?php

namespace AvegaCms\Database\Seeds;

use AvegaCms\Models\Admin\{UserModel, UserRolesModel};
use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use AvegaCms\Entities\{UserEntity, UserRolesEntity};
use ReflectionException;

class UsersSeeder extends Seeder
{
    /**
     * @throws ReflectionException
     */
    public function run()
    {
        $UM = model(UserModel::class);
        $URM = model(UserRolesModel::class);

        $EU = new UserEntity();
        $URE = new UserRolesEntity();

        $fakeUsers = (new Fabricator($UM, null))->make(100);

        foreach ($fakeUsers as $item) {
            if ($id = $UM->insert($EU->fill($item->toArray()))) {
                $URM->save(
                    $URE->fill(
                        [
                            'role_id'       => rand(2, 4),
                            'user_id'       => $id,
                            'created_by_id' => 1
                        ]
                    )
                );
            }
        }
    }
}
