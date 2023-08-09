<?php

namespace AvegaCms\Database\Seeds;

use AvegaCms\Models\Admin\UserModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use AvegaCms\Entities\UserEntity;

class UsersSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $UM = new UserModel();
        $EU = new UserEntity();

        $fakeUsers = (new Fabricator($UM, null))->make(10);

        foreach ($fakeUsers as $item) {
            $UM->save($EU->fill($item->toArray()));
        }
    }
}
