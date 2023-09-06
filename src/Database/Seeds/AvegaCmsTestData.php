<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use Config\Database;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Models\Admin\{
    UserModel,
    ContentModel,
    MetaDataModel,
    ModulesModel,
    SettingsModel,
    LoginModel,
    RolesModel,
    UserRolesModel,
    PermissionsModel,
    LocalesModel
};
use AvegaCms\Entities\{
    ContentEntity,
    MetaDataEntity,
    ModulesEntity,
    LoginEntity,
    RolesEntity,
    SettingsEntity,
    UserEntity,
    UserRolesEntity,
    PermissionsEntity,
    LocalesEntity
};
use ReflectionException;
use Exception;

class AvegaCmsTestData extends Seeder
{
    protected UserModel      $UM;
    protected ContentModel   $CM;
    protected MetaDataModel  $MDM;
    protected ModulesModel   $MM;
    protected LoginModel     $LM;
    protected SettingsModel  $SM;
    protected RolesModel     $RM;
    protected UserRolesModel $URM;

    protected PermissionsModel $PM;

    protected LocalesModel $LLM;

    protected $settings = [];

    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        parent::__construct($config, $db);

        helper(['avegacms', 'date']);

        $this->MM = model(ModulesModel::class);
        $this->LM = model(LoginModel::class);
        $this->UM = model(UserModel::class);
        $this->CM = model(ContentModel::class);
        $this->MDM = model(MetaDataModel::class);
        $this->SM = model(SettingsModel::class);
        $this->RM = model(RolesModel::class);
        $this->PM = model(PermissionsModel::class);
        $this->URM = model(UserRolesModel::class);
        $this->LLM = model(LocalesModel::class);
    }

    public function run()
    {
        $this->createUsers();
        $this->createPages();
    }

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
    protected function createUsers(): void
    {
        if (
            CLI::prompt('Create new users?', ['y', 'n']) === 'y' &&
            ($num = CLI::prompt(
                'How many users do you want to create?',
                null,
                ['required', 'is_natural_no_zero']
            )
            )
        ) {
            $UE = new UserEntity();
            $URE = new UserRolesEntity();
            $roles = $this->RM->where(['id !=' => 1])->findColumn('id');

            $fakeUsers = (new Fabricator($this->UM, null))->make($num);

            foreach ($fakeUsers as $item) {
                if ($id = $this->UM->insert($UE->fill($item->toArray()))) {
                    $this->URM->save(
                        $URE->fill(
                            [
                                'role_id'       => $roles[array_rand($roles)],
                                'user_id'       => $id,
                                'created_by_id' => 1
                            ]
                        )
                    );
                }
            }
        }
    }

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
    protected function createPages(): void
    {
        if (CLI::prompt('Create new pages?', ['y', 'n']) === 'y' && ($num = CLI::prompt(
                'How many pages do you want to create?',
                null,
                ['required', 'is_natural_no_zero']
            )) && ($nesting = CLI::prompt(
                'What is the maximum nesting of pages?',
                null,
                ['required', 'is_natural_no_zero']
            ))
        ) {
            $useMultiLocales = settings('core.env.useMultiLocales');

            $locales = $this->LLM->where([
                'active' => 1, ...(! $useMultiLocales ? ['is_default' => 1] : [])
            ])->findColumn('id');

            $mainPages = [];


            foreach ($locales as $locale) {
                $mainPages[] = $this->_createMetaData(MetaDataTypes::Main->value, $locale);
            }
        }
    }

    /**
     * @param  string  $type
     * @param  int  $locale
     * @param  int  $creator
     * @param  int  $module
     * @param  int  $parent
     * @param  int  $item_id
     * @return int
     * @throws ReflectionException
     */
    private function _createMetaData(
        string $type,
        int $locale = 1,
        int $creator = 1,
        int $module = 0,
        int $parent = 0,
        int $item_id = 0
    ): int {
        $meta = (new Fabricator($this->MDM, null))->make(1);
        d($meta, $type);
        $meta->meta_type = $type;
        $meta->locale_id = $locale;
        $meta->creator_id = $creator;
        $meta->module_id = $module;
        $meta->parent = $parent;
        $meta->item_id = $item_id;
        dd($meta);
        if ($metaId = $this->MDM->insert($meta)) {
            $this->CM->insert((new Fabricator($this->CM, null))->make(1));
        }

        return $metaId;
    }

}
