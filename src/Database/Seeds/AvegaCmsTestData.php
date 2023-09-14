<?php

namespace AvegaCms\Database\Seeds;

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
    LocalesModel,
    PostRubricsModel
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
    LocalesEntity,
    PostRubricsEntity
};
use ReflectionException;
use Exception;

class AvegaCmsTestData extends Seeder
{
    protected UserModel        $UM;
    protected ContentModel     $CM;
    protected MetaDataModel    $MDM;
    protected ModulesModel     $MM;
    protected LoginModel       $LM;
    protected SettingsModel    $SM;
    protected RolesModel       $RM;
    protected UserRolesModel   $URM;
    protected LocalesModel     $LLM;
    protected PostRubricsModel $PRM;

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
        $this->SM = model(SettingsModel::class);
        $this->RM = model(RolesModel::class);
        $this->MDM = model(MetaDataModel::class);
        $this->URM = model(UserRolesModel::class);
        $this->LLM = model(LocalesModel::class);
        $this->PRM = model(PostRubricsModel::class);
    }

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
    public function run()
    {
        $this->createUsers();
        $this->createPages();
        $this->createRubrics();
        $this->createPosts();
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
                // Создание главной страницы
                $mainId = $this->_createMetaData(
                    type: MetaDataTypes::Main->value,
                    locale: $locale,
                    status: MetaStatuses::Publish->value
                );

                // Создание 404 страницы
                $this->_createMetaData(
                    type: MetaDataTypes::Page404->value,
                    locale: $locale,
                    parent: $mainId,
                    status: MetaStatuses::Publish->value
                );
                $this->_createSubPages($num, $nesting, $locale, $mainId);
            }

            $this->MDM->update(['meta_type' => MetaDataTypes::Main->value], ['in_sitemap' => 1]);
        }
    }

    protected function createRubrics(): void
    {
        if ($categories = CLI::prompt(
            'How many rubrics do you want to create?',
            null,
            ['required', 'is_natural_no_zero']
        )) {
            $useMultiLocales = settings('core.env.useMultiLocales');
            $locales = $this->LLM->where([
                'active' => 1, ...(! $useMultiLocales ? ['is_default' => 1] : [])
            ])->findColumn('id');

            foreach ($locales as $locale) {
                for ($i = 0; $categories > $i; $i++) {
                    $this->_createMetaData(MetaDataTypes::Rubric->value, $locale);
                }
            }
            CLI::newLine();
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    protected function createPosts(): void
    {
        if (
            CLI::prompt('Create new posts?', ['y', 'n']) === 'y' &&
            ($num = CLI::prompt(
                'How many posts do you want to create?',
                null,
                ['required', 'is_natural_no_zero']
            ))
        ) {
            $useMultiLocales = settings('core.env.useMultiLocales');

            $locales = $this->LLM->where([
                'active' => 1, ...(! $useMultiLocales ? ['is_default' => 1] : [])
            ])->findColumn('id');

            foreach ($locales as $locale) {
                $j = 1;
                for ($i = 0; $num > $i; $i++) {
                    CLI::showProgress($j++, $num);
                    $this->_createMetaData(MetaDataTypes::Post->value, $locale);
                }
                CLI::showProgress(false);
                CLI::newLine();
            }

            foreach ($locales as $locale) {
                $rubricsId = $this->MDM->where(
                    [
                        'locale_id' => $locale,
                        'meta_type' => MetaDataTypes::Rubric->value
                    ]
                )->findColumn('id');

                $postsId = array_unique($this->MDM->where(
                    [
                        'locale_id' => $locale,
                        'meta_type' => MetaDataTypes::Post->value
                    ]
                )->findColumn('id'));

                $PCE = new PostRubricsEntity();

                $postCategories = [];

                foreach ($postsId as $postId) {
                    $num = array_rand($rubricsId, rand(1, count($rubricsId)));
                    $num = array_unique(! is_array($num) ? [$num] : $num);
                    foreach ($num as $k => $c) {
                        $postCategories[] = $PCE->fill(
                            [
                                'post_id'   => $postId,
                                'rubric_id' => $rubricsId[$c],
                                'is_main'   => ($k === 0) ? 1 : 0
                            ]
                        )->toArray();
                    }
                }

                $this->PRM->insertBatch(array_unique($postCategories, SORT_REGULAR));
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

        if ($type === MetaDataTypes::Main->value) {
            $meta['url'] = '';
            $meta['slug'] = 'main';
        }

        if ($type === MetaDataTypes::Page404->value) {
            $meta['url'] = $meta['slug'] = 'page-not-found';
        }

        if ( ! is_null($status)) {
            $meta['status'] = $status;
        }

        if ($metaId = $this->MDM->insert((new MetaDataEntity($meta)))) {
            $content = (new Fabricator($this->CM, null))->makeArray();
            $content['id'] = $metaId;
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
    private function _getParentPageId(int $locale, int $level): int|null
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
