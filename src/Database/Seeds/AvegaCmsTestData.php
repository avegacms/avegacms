<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use Config\Database;
use AvegaCms\Enums\{MetaDataTypes, MetaStatuses};
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

    protected array $settings = [];

    protected int $numPages = 0;

    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        parent::__construct($config, $db);

        helper(['avegacms', 'date', 'array']);

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

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
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

            $count = count($fakeUsers);
            $i = 1;

            foreach ($fakeUsers as $item) {
                CLI::showProgress($i++, $count);
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
            CLI::showProgress(false);
            CLI::newLine();
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

            $this->numPages = $num;

            foreach ($locales as $locale) {
                // Создание главных страниц
                $mainId = $this->_createMetaData(
                    MetaDataTypes::Main->value,
                    $locale,
                    1,
                    0,
                    0,
                    0,
                    MetaStatuses::Publish->value
                );
                $this->_createSubPages($num, $nesting, $locale, $mainId);
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
     * @throws Exception|ReflectionException
     */
    private function _createMetaData(
        string $type,
        int $locale = 1,
        int $creator = 1,
        int $module = 0,
        int $parent = 0,
        int $item_id = 0,
        ?string $status = null
    ): int {
        $meta = (new Fabricator($this->MDM, null))->makeArray();

        $meta['meta_type'] = $type;
        $meta['locale_id'] = $locale;
        $meta['creator_id'] = $creator;
        $meta['module_id'] = $module;
        $meta['parent'] = $parent;
        $meta['item_id'] = $item_id;

        if ( ! is_null($status)) {
            $meta['status'] = $status;
        }

        if ($metaId = $this->MDM->insert((new MetaDataEntity($meta)))) {
            $content = (new Fabricator($this->CM, null))->makeArray();
            $content['meta_id'] = $metaId;
            $this->CM->insert((new ContentEntity($content)));
        }

        return $metaId;
    }

    /**
     * @param  int  $num
     * @param  int  $nesting
     * @param  int  $locale
     * @param  int  $parent
     * @return void
     * @throws Exception|ReflectionException
     */
    private function _createSubPages(int $num, int $nesting, int $locale, int $parent): void
    {
        if ($num > 0) {
            if ($this->numPages === $num) {
                $subId = $this->_createMetaData(
                    MetaDataTypes::Page->value,
                    $locale,
                    1,
                    0,
                    $parent,
                    0
                );

                $num--;

                $this->_createSubPages(
                    $num,
                    $nesting,
                    $locale,
                    ($nesting > 1) ? $subId : $parent
                );
            } else {
                if ($nesting > 1) {
                    $parentId = $this->_getParentPageId($locale, rand(0, $nesting));
                    d($parentId);
                    if ($parentId !== null) {
                        $subId = $this->_createMetaData(MetaDataTypes::Page->value, $locale, 1, 0, $parentId, 0);
                    } else {
                        $subId = $this->_createMetaData(MetaDataTypes::Page->value, $locale, 1, 0, $parent, 0);
                    }
                    $num--;
                    $this->_createSubPages($num, $nesting, $locale, $subId);
                }
            }
        }
    }

    /**
     * @param  int  $locale
     * @param  int  $level
     * @return int|null
     */
    public function _getParentPageId(int $locale, int $level): int|null
    {
        $object = $this->MDM->select(['id', 'parent'])
            ->where(['locale_id' => $locale, 'module_id' => 0, 'item_id' => 0])
            ->whereIn('meta_type', [MetaDataTypes::Page->value, MetaDataTypes::Main->value])
            ->findAll();

        $list = [];

        foreach ($object as $item) {
            $list[] = $item->toArray();
        }

        $list = getTree($list);

        if ($level === 0) {
            return $list[0]['id'] ?? null;
        }

        $parent = dot_array_search(str_repeat('*.list', $level - 1), $list);

        return ! is_null($parent) ? ($parent[array_rand($parent)]['id'] ?? null) : null;
    }
}
