<?php

namespace AvegaCms\Database\Seeds;

use CodeIgniter\Test\Fabricator;
use AvegaCms\Enums\{SettingsReturnTypes, MetaDataTypes, MetaStatuses, UserStatuses};
use AvegaCms\Utils\Cms;
use CodeIgniter\Database\{BaseConnection, Seeder};
use Config\Database;
use CodeIgniter\CLI\CLI;
use AvegaCms\Config\AvegaCms;
use AvegaCms\Models\Admin\{ModulesModel,
    MetaDataModel,
    ContentModel,
    SettingsModel,
    LoginModel,
    RolesModel,
    UserRolesModel,
    PermissionsModel,
    LocalesModel,
    EmailTemplateModel
};
use AvegaCms\Entities\{ContentEntity,
    MetaDataEntity,
    ModulesEntity,
    LoginEntity,
    RolesEntity,
    SettingsEntity,
    UserRolesEntity,
    PermissionsEntity,
    LocalesEntity,
    EmailTemplateEntity
};
use ReflectionException;
use Exception;

class AvegaCmsInstallSeeder extends Seeder
{
    protected string             $version  = AvegaCms::AVEGACMS_VERSION;
    protected ModulesModel       $MM;
    protected ContentModel       $CM;
    protected LoginModel         $LM;
    protected SettingsModel      $SM;
    protected RolesModel         $RM;
    protected UserRolesModel     $URM;
    protected PermissionsModel   $PM;
    protected LocalesModel       $LLM;
    protected EmailTemplateModel $ETM;
    protected MetaDataModel      $MDM;
    protected int                $numPages = 0;

    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        parent::__construct($config, $db);

        $this->MM  = model(ModulesModel::class);
        $this->LM  = model(LoginModel::class);
        $this->SM  = model(SettingsModel::class);
        $this->RM  = model(RolesModel::class);
        $this->PM  = model(PermissionsModel::class);
        $this->CM  = model(ContentModel::class);
        $this->URM = model(UserRolesModel::class);
        $this->LLM = model(LocalesModel::class);
        $this->ETM = model(EmailTemplateModel::class);
        $this->MDM = model(MetaDataModel::class);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function run(): void
    {
        cache()->clean();

        $this->_createSettings();
        $userId = $this->_createUser();
        $this->_createRoles($userId);
        $this->_createUserRoles($userId);
        $this->_installCmsModules($userId);
        $this->_createPermissions($userId);
        $this->_createLocales($userId);
        $this->_createEmailSystemTemplate($userId);
        $this->_setLocales();
        $this->_createPages();
        $this->_createPublicFolders();

        cache()->clean();
    }

    /**
     * @return int
     * @throws ReflectionException
     */
    private function _createUser(): int
    {
        return $this->LM->insert(
            (new LoginEntity(
                [
                    'login'    => 'admin',
                    'email'    => 'admin@avegacms.ru',
                    'password' => '123Qwe78',
                    'status'   => UserStatuses::Active->value
                ]
            ))
        );
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createRoles(int $userId): void
    {
        $roles = [
            [
                'role'          => 'root',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 1,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'admin',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 2,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'manager',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 3,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'role'          => 'default',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 4,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        $rolesEntity = new RolesEntity();

        foreach ($roles as $role) {
            $this->RM->insert($rolesEntity->fill($role));
        }
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createUserRoles(int $userId): void
    {
        $this->URM->insert(
            (new UserRolesEntity())->fill(
                [
                    'role_id'       => 1,
                    'user_id'       => $userId,
                    'created_by_id' => $userId,
                ]
            )
        );
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _installCmsModules(int $userId): void
    {
        $modules = [
            [
                'parent'        => 0,
                'is_core'       => 1,
                'is_plugin'     => 0,
                'is_system'     => 0,
                'key'           => 'settings',
                'slug'          => 'settings',
                'name'          => 'Cms.modules.name.settings',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.settings',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_core'       => 1,
                'is_plugin'     => 0,
                'is_system'     => 0,
                'key'           => 'content',
                'slug'          => 'content',
                'name'          => 'Cms.modules.name.content',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.content',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_core'       => 1,
                'is_plugin'     => 1,
                'is_system'     => 1,
                'key'           => 'content_builder',
                'slug'          => 'content_builder',
                'name'          => 'Cms.modules.name.content_builder',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.content_builder',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            [
                'parent'        => 0,
                'is_core'       => 1,
                'is_plugin'     => 1,
                'is_system'     => 1,
                'key'           => 'uploader',
                'slug'          => 'uploader',
                'name'          => 'Cms.modules.name.uploader',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.uploader',
                'extra'         => '',
                'in_sitemap'    => 0,
                'active'        => 1,
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        foreach ($modules as $module) {
            $modulesEntity[] = (new ModulesEntity($module));
        }

        $this->MM->insertBatch($modulesEntity);

        unset($modulesEntity);

        $subModules = $this->MM->select(['id', 'slug'])->whereIn('slug', ['settings', 'content'])->findAll();

        $list = [];

        foreach ($subModules as $subModule) {
            $list[$subModule->slug] = $subModule->id;
        }

        $modules = [

            'settings' => [
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.roles',
                    'slug'          => 'roles',
                    'name'          => 'Cms.modules.name.roles',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.roles',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.permissions',
                    'slug'          => 'permissions',
                    'name'          => 'Cms.modules.name.permissions',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.permissions',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.users',
                    'slug'          => 'users',
                    'name'          => 'Cms.modules.name.users',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.users',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.modules',
                    'slug'          => 'modules',
                    'name'          => 'Cms.modules.name.modules',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.modules',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.locales',
                    'slug'          => 'locales',
                    'name'          => 'Cms.modules.name.locales',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.locales',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'settings.seo',
                    'slug'          => 'seo',
                    'name'          => 'Cms.modules.name.seo',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.seo',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 1,
                    'key'           => 'settings.settings',
                    'slug'          => 'settings',
                    'name'          => 'Cms.modules.name.settings',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.settings',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'settings.navigations',
                    'slug'          => 'navigations',
                    'name'          => 'Cms.modules.name.navigations',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.navigations',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'settings.email_template',
                    'slug'          => 'email_template',
                    'name'          => 'Cms.modules.name.email_template',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.email_template',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ]
            ],
            'content'  => [
                [
                    'parent'        => $list['content'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'content.rubrics',
                    'slug'          => 'rubrics',
                    'name'          => 'Cms.modules.name.rubrics',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.rubrics',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'content.pages',
                    'slug'          => 'pages',
                    'name'          => 'Cms.modules.name.pages',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.pages',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'content.posts',
                    'slug'          => 'posts',
                    'name'          => 'Cms.modules.name.posts',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.posts',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ],
                [
                    'parent'        => $list['content'],
                    'is_core'       => 1,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => 'content.tags',
                    'slug'          => 'tags',
                    'name'          => 'Cms.modules.name.tags',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.tags',
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0
                ]
            ]
        ];

        $modulesEntity = [];

        foreach ($subModules as $subModule) {
            foreach ($modules as $slug => $list) {
                foreach ($list as $item) {
                    if ($slug === $subModule->slug) {
                        $item['parent']  = $subModule->id;
                        $modulesEntity[] = (new ModulesEntity($item));
                    }
                }
            }
        }

        $this->MM->insertBatch($modulesEntity);
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createPermissions(int $userId): void
    {
        $permissions = [
            // Default permission Module
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 1,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default permission System
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 1,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default permission Plugin
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 0,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // root Module
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 1,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // root System
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 1,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // root Plugin
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Admin Module
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 1,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Admin Plugin
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 1,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Manager Module
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 1,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Manager Plugin
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 1,
                'read'          => 1,
                'update'        => 1,
                'delete'        => 1,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],

            // Default Module
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 0,
                'slug'          => '',
                'access'        => 1,
                'self'          => 1,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ],
            // Default Plugin
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => 0,
                'is_system'     => 0,
                'is_plugin'     => 1,
                'slug'          => '',
                'access'        => 1,
                'self'          => 0,
                'create'        => 0,
                'read'          => 0,
                'update'        => 0,
                'delete'        => 0,
                'moderated'     => 0,
                'settings'      => 0,
                'extra'         => '',
                'created_by_id' => $userId,
                'updated_by_id' => 0
            ]
        ];

        foreach ($permissions as $permission) {
            $defPermissions[] = (new PermissionsEntity($permission));
        }

        $this->PM->insertBatch($defPermissions);

        $modules = $this->MM->select(['id', 'parent', 'is_system', 'is_plugin', 'slug'])->findAll();
        $roles   = $this->RM->select(['id', 'role'])->findAll();

        foreach ($modules as $module) {
            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    if ($permission['role_id'] === $role->id && $module->is_system === $permission['is_system'] && $module->is_plugin === $permission['is_plugin']) {
                        $perm              = $permission;
                        $perm['parent']    = $module->parent;
                        $perm['module_id'] = $module->id;
                        $perm['slug']      = $module->slug;

                        $moduleRolePermission[] = (new PermissionsEntity($perm));
                    }
                }
            }
        }

        $this->PM->insertBatch($moduleRolePermission ?? null);
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createLocales(int $userId): void
    {
        $locales = [
            [
                'slug'          => 'ru',
                'locale'        => 'ru_RU',
                'locale_name'   => 'Русская версия',
                'home'          => 'Главная',
                'extra'         => [
                    'app_name' => 'Мой новый проект',
                    'og:image' => 'ogDefaultRu.jpg'
                ],
                'is_default'    => 1,
                'active'        => 1,
                'created_by_id' => $userId
            ],
            [
                'slug'          => 'en',
                'locale'        => 'en_EN',
                'locale_name'   => 'English version',
                'home'          => 'Home',
                'extra'         => [
                    'app_name' => 'My new project',
                    'og:image' => 'ogDefaultEn.jpg'
                ],
                'is_default'    => 0,
                'active'        => 1,
                'created_by_id' => $userId
            ],
            [
                'slug'          => 'de',
                'locale'        => 'de_DE',
                'locale_name'   => 'Deutsche version',
                'home'          => 'Startseite',
                'extra'         => [
                    'app_name' => 'Mein neues Projekt',
                    'og:image' => 'ogDefaultDe.jpg'
                ],
                'is_default'    => 0,
                'active'        => 1,
                'created_by_id' => $userId
            ]
        ];

        foreach ($locales as $locale) {
            $localesEntity[] = (new LocalesEntity($locale));
        }

        $this->LLM->insertBatch($localesEntity);
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    private function _createSettings(): void
    {
        $settingsList = [
            // .env
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'timezone',
                'value'         => 'Europe/Moscow',
                'default_value' => 'Europe/Moscow',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.env.timezone',
                'context'       => 'Settings.context.env.timezone'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'secretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.env.secretKey',
                'context'       => 'Settings.context.env.secretKey'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'defLocale',
                'value'         => 'ru',
                'default_value' => 'ru',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.env.defLocale',
                'context'       => 'Settings.context.env.defLocale'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useMultiLocales',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useMultiLocales',
                'context'       => 'Settings.context.env.useMultiLocales'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useFrontend',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useFrontend',
                'context'       => 'Settings.context.env.useFrontend'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useViewData',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useViewData',
                'context'       => 'Settings.context.env.useViewData'
            ],

            // auth
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useCors',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useCors',
                'context'       => 'Settings.context.auth.useCors'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useSession',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useSession',
                'context'       => 'Settings.context.auth.useSession'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'allowPreRegistration',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.allowPreRegistration',
                'context'       => 'Settings.context.auth.allowPreRegistration'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 0,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useToken',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useToken',
                'context'       => 'Settings.context.auth.useToken'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useJwt',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useJwt',
                'context'       => 'Settings.context.auth.useJwt'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSecretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.jwtSecretKey',
                'context'       => 'Settings.context.auth.jwtSecretKey'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSessionsLimit',
                'value'         => 3,
                'default_value' => 3,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtSessionsLimit',
                'context'       => 'Settings.context.auth.jwtSessionsLimit'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtLiveTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtRefreshTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtAlg',
                'value'         => 'HS256',
                'default_value' => 'HS256',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.jwtAlg',
                'context'       => 'Settings.context.auth.jwtAlg'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useWhiteIpList',
                'value'         => 1,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useWhiteIpList',
                'context'       => 'Settings.context.auth.useWhiteIpList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'whiteIpList',
                'value'         => serialize([]),
                'default_value' => serialize([]),
                'return_type'   => SettingsReturnTypes::Array->value,
                'label'         => 'Settings.label.auth.whiteIpList',
                'context'       => 'Settings.context.auth.whiteIpList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginTypeList',
                'value'         => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone'
                    ]
                ),
                'default_value' => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone'
                    ]
                ),
                'return_type'   => SettingsReturnTypes::Array->value,
                'label'         => 'Settings.label.auth.loginTypeList',
                'context'       => 'Settings.context.auth.loginTypeList'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginType',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.loginType',
                'context'       => 'Settings.context.auth.loginType'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'use2fa',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.use2fa',
                'context'       => 'Settings.context.auth.use2fa'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => '2faField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.2faField',
                'context'       => 'Settings.context.auth.2faField'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeLength',
                'value'         => 4,
                'default_value' => 4,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.verifyCodeLength',
                'context'       => 'Settings.context.auth.verifyCodeLength'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeTime',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.verifyCodeTime',
                'context'       => 'Settings.context.auth.verifyCodeTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useRecovery',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useRecovery',
                'context'       => 'Settings.context.auth.useRecovery'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.recoveryField',
                'context'       => 'Settings.context.auth.recoveryField'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryCodeTime',
                'value'         => 10,
                'default_value' => 10,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.recoveryCodeTime',
                'context'       => 'Settings.context.auth.recoveryCodeTime'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'authSmsMessage',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.authSmsMessage',
                'context'       => 'Settings.context.auth.authSmsMessage'
            ],

            // Email
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'fromEmail',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.fromEmail',
                'context'       => 'Settings.context.email.fromEmail'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'fromName',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.fromName',
                'context'       => 'Settings.context.email.fromName'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'replyEmail',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.replyEmail',
                'context'       => 'Settings.context.email.replyEmail'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'replyName',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.replyName',
                'context'       => 'Settings.context.email.replyName'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'returnEmail',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.returnEmail',
                'context'       => 'Settings.context.email.returnEmail'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'userAgent',
                'value'         => '*',
                'default_value' => '*',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.userAgent',
                'context'       => 'Settings.context.email.userAgent'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'charset',
                'value'         => 'UTF-8',
                'default_value' => 'UTF-8',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.charset',
                'context'       => 'Settings.context.email.charset'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'protocol',
                'value'         => 'mail',
                'default_value' => 'mail',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.protocol',
                'context'       => 'Settings.context.email.protocol'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'priority',
                'value'         => 1,
                'default_value' => 3,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.priority',
                'context'       => 'Settings.context.email.priority'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'mailType',
                'value'         => 'html',
                'default_value' => 'html',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.mailType',
                'context'       => 'Settings.context.email.mailType'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpHost',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpHost',
                'context'       => 'Settings.context.email.smtpHost'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpUser',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpUser',
                'context'       => 'Settings.context.email.smtpUser'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpPass',
                'value'         => '',
                'default_value' => '',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpPass',
                'context'       => 'Settings.context.email.smtpPass'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpPort',
                'value'         => 465,
                'default_value' => 465,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.smtpPort',
                'context'       => 'Settings.context.email.smtpPort'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpTimeout',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.smtpTimeout',
                'context'       => 'Settings.context.email.smtpTimeout'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpKeepalive',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.email.smtpKeepalive',
                'context'       => 'Settings.context.email.smtpKeepalive'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpCrypto',
                'value'         => 'ssl',
                'default_value' => 'ssl',
                'return_type'   => SettingsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpCrypto',
                'context'       => 'Settings.context.email.smtpCrypto'
            ],

            // Content
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'content',
                'slug'          => 'posts',
                'key'           => 'postsPerPage',
                'value'         => 20,
                'default_value' => 20,
                'return_type'   => SettingsReturnTypes::Integer->value,
                'label'         => 'Settings.label.posts.postsPerPage',
                'context'       => 'Settings.context.posts.postsPerPage'
            ],
            [
                'module_id'     => 0,
                'is_core'       => 1,
                'entity'        => 'content',
                'slug'          => 'posts',
                'key'           => 'showAuthorPost',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => SettingsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.posts.showAuthorPost',
                'context'       => 'Settings.context.posts.showAuthorPost'
            ],
        ];
        $list         = [];

        foreach ($settingsList as $item) {
            $list[] = (new SettingsEntity($item));
        }
        $this->SM->insertBatch($list);
    }

    /**
     * @param  int  $userId
     * @return void
     * @throws ReflectionException
     */
    private function _createEmailSystemTemplate(int $userId): void
    {
        $templates = [
            'ru' => [
                [
                    'label'     => 'Подтверждение email',
                    'slug'      => 'confirm',
                    'subject'   => '{siteName} подтверждение email',
                    'content'   => 'Ваш код подтверждения email {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Подтверждение кода авторизации',
                    'slug'      => 'auth',
                    'subject'   => 'Код авторизации',
                    'content'   => 'Ваш код авторизации {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Подтверждение кода восстановления доступов',
                    'slug'      => 'recovery',
                    'subject'   => 'Восстановление доступов',
                    'content'   => 'Ваш код подтверждения восстановления доступов {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ]
            ],
            'en' => [
                [
                    'label'     => 'Email confirmation',
                    'slug'      => 'confirm',
                    'subject'   => '{siteName} confirmation of email',
                    'content'   => 'Your confirmation email code {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Authorization code confirmation',
                    'slug'      => 'auth',
                    'subject'   => 'Authorization code',
                    'content'   => 'Your authorization code {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Restoring access',
                    'slug'      => 'recovery',
                    'subject'   => 'Restoring access',
                    'content'   => 'Your access recovery confirmation code {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ]
            ],
            'de' => [
                [
                    'label'     => 'E-Mail-Bestätigung',
                    'slug'      => 'confirm',
                    'subject'   => '{siteName} E-Mail-Bestätigung',
                    'content'   => 'Ihr Bestätigungscode email {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Bestätigung des Autorisierungscodes',
                    'slug'      => 'auth',
                    'subject'   => 'Autorisierungscode',
                    'content'   => 'Ihr Autorisierungscode ist {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ],
                [
                    'label'     => 'Bestätigung des Zugriffs-Wiederherstellungscodes',
                    'slug'      => 'recovery',
                    'subject'   => 'Zugriff wiederherstellen',
                    'content'   => 'Ihr Zugangswiederherstellungsbestätigungscode ist {code}',
                    'variables' => '',
                    'template'  => 'auth'
                ]
            ]
        ];

        $locales = $this->LLM->select(['id', 'slug'])
            ->orderBy('id', 'ASC')
            ->findAll();

        $emailTemplate = [];

        foreach ($locales as $locale) {
            foreach ($templates[$locale->slug] as $template) {
                $template['locale_id']     = $locale->id;
                $template['is_system']     = 1;
                $template['created_by_id'] = $userId;
                $emailTemplate[]           = (new EmailTemplateEntity($template));
            }
        }

        $this->ETM->insertBatch($emailTemplate);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function _setLocales(): void
    {
        if (CLI::prompt('Use multi locales?', ['y', 'n']) === 'y') {
            Cms::settings('core.env.useMultiLocales', 1);
        }
        CLI::newLine();
    }

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
    private function _createPages(): void
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
            $useMultiLocales = Cms::settings('core.env.useMultiLocales');

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

            CLI::newLine();
        }
    }

    /**
     * @return void
     */
    private function _createPublicFolders(): void
    {
        $directories = [
            'uploads',
            'uploads/users',
            'uploads/locales',
            'uploads/content',
            'uploads/content/thumbs'
        ];

        foreach ($directories as $directory) {
            if ( ! is_dir($directory = FCPATH . $directory) && mkdir($directory, 0777, true)) {
                file_put_contents($directory . '/index.html', '');
            } else {
                CLI::write('Can\'t create directory: ' . $directory);
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
     * @param  string|null  $status
     * @param  string|null  $url
     * @return int
     * @throws ReflectionException
     */
    private function _createMetaData(
        string $type,
        int $locale = 1,
        int $creator = 1,
        int $module = 0,
        int $parent = 0,
        int $item_id = 0,
        ?string $status = null,
        ?string $url = null
    ): int {
        $meta = (new Fabricator($this->MDM, null))->makeArray();

        $meta['meta_type']       = $type;
        $meta['locale_id']       = $locale;
        $meta['creator_id']      = $creator;
        $meta['module_id']       = $module;
        $meta['parent']          = $parent;
        $meta['item_id']         = $item_id;
        $meta['use_url_pattern'] = 0;

        if ($type === MetaDataTypes::Main->value) {
            $meta['url']  = '';
            $meta['slug'] = 'main';
        }

        if (in_array($type, [MetaDataTypes::Rubric->value, MetaDataTypes::Post->value])) {
            $meta['url'] = $url . '/' . $meta['slug'];
        }

        if ($type === MetaDataTypes::Page404->value) {
            $meta['url'] = $meta['slug'] = 'page-not-found';
        }

        if ( ! is_null($status)) {
            $meta['status'] = $status;
        }

        if ($metaId = $this->MDM->insert((new MetaDataEntity($meta)))) {
            $content       = (new Fabricator($this->CM, null))->makeArray();
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
                    type: MetaDataTypes::Page->value,
                    locale: $locale,
                    parent: $parent,
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
                        $subId = $this->_createMetaData(
                            type: MetaDataTypes::Page->value,
                            locale: $locale,
                            parent: $parentId
                        );
                    } else {
                        $subId = $this->_createMetaData(
                            type: MetaDataTypes::Page->value,
                            locale: $locale,
                            parent: $parent
                        );
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

        $list = Cms::getTree($list);

        if ($level === 0) {
            return $list[0]['id'] ?? null;
        }

        $parent = dot_array_search(str_repeat('*.list', $level - 1), $list);

        return ! is_null($parent) ? ($parent[array_rand($parent)]['id'] ?? null) : null;
    }
}
