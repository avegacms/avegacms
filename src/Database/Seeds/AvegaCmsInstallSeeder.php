<?php

declare(strict_types=1);

namespace AvegaCms\Database\Seeds;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Database\Factories\MetaContentFactory;
use AvegaCms\Database\Factories\MetaDataFactory;
use AvegaCms\Enums\FieldsReturnTypes;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Enums\UserStatuses;
use AvegaCms\Libraries\Content\Content;
use AvegaCms\Libraries\Content\Exceptions\ContentExceptions;
use AvegaCms\Models\Admin\ContentModel;
use AvegaCms\Models\Admin\EmailTemplateModel;
use AvegaCms\Models\Admin\LocalesModel;
use AvegaCms\Models\Admin\LoginModel;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Models\Admin\PermissionsModel;
use AvegaCms\Models\Admin\RolesModel;
use AvegaCms\Models\Admin\SettingsModel;
use AvegaCms\Models\Admin\UserModel;
use AvegaCms\Models\Admin\UserRolesModel;
use AvegaCms\Utilities\Cms;
use AvegaCms\Utilities\CmsFileManager;
use AvegaCms\Utilities\CmsModule;
use AvegaCms\Utilities\Exceptions\UploaderException;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use Exception;
use ReflectionException;

class AvegaCmsInstallSeeder extends Seeder
{
    protected string $version      = AvegaCms::AVEGACMS_VERSION;
    protected int $numPages        = 0;
    protected int $contentModuleId = 0;
    protected ModulesModel $MM;
    protected ContentModel $CM;
    protected LoginModel $LM;
    protected SettingsModel $SM;
    protected RolesModel $RM;
    protected UserRolesModel $URM;
    protected PermissionsModel $PM;
    protected LocalesModel $LLM;
    protected EmailTemplateModel $ETM;
    protected MetaDataModel $MDM;

    /**
     * @throws ReflectionException
     * @throws UploaderException
     */
    public function run(): void
    {
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

        cache()->clean();

        $this->_createSettings();
        $userId = $this->_createUser();
        $this->_createRoles($userId);
        $this->_createUserRoles($userId);
        $this->_installCmsModules($userId);
        $this->_createPermissions($userId);
        $this->_createLocales($userId);
        $this->_createEmailSystemTemplate($userId);
        // $this->_setLocales();
        $this->_createMainPages();
        $this->_createPages();
        $this->_createDefaultActions();
        $this->_fileManagerRegistration();
        $this->_createPublicFolders();

        cache()->clean();
    }

    /**
     * @throws ReflectionException
     */
    private function _createUser(): int
    {
        return (new UserModel())->insert(
            [
                'login'    => 'admin',
                'email'    => 'admin@avegacms.ru',
                'password' => '123Qwe$78',
                'status'   => UserStatuses::Active->value,
            ]
        );
    }

    /**
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
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'role'          => 'admin',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 2,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'role'          => 'manager',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 3,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'role'          => 'default',
                'description'   => '',
                'color'         => '#',
                'path'          => '/',
                'priority'      => 4,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
        ];

        foreach ($roles as $role) {
            if ($this->RM->insert($role) === false) {
                d($this->RM->errors());
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function _createUserRoles(int $userId): void
    {
        $this->URM->insert(
            [
                'role_id'       => 1,
                'user_id'       => $userId,
                'created_by_id' => $userId,
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    private function _installCmsModules(int $userId): void
    {
        $modules = [
            [
                'parent'        => 0,
                'is_core'       => true,
                'is_plugin'     => false,
                'is_system'     => false,
                'key'           => 'settings',
                'slug'          => 'settings',
                'class_name'    => '',
                'name'          => 'Cms.modules.name.settings',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.settings',
                'extra'         => [],
                'in_sitemap'    => false,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'parent'        => 0,
                'is_core'       => true,
                'is_plugin'     => false,
                'is_system'     => false,
                'key'           => 'pages',
                'slug'          => 'pages',
                'class_name'    => 'Pages',
                'name'          => 'Cms.modules.name.content',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.content',
                'extra'         => [],
                'in_sitemap'    => true,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'parent'        => 0,
                'is_core'       => true,
                'is_plugin'     => true,
                'is_system'     => true,
                'key'           => 'builder',
                'slug'          => 'builder',
                'class_name'    => 'Builder',
                'name'          => 'Cms.modules.name.builder',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.builder',
                'extra'         => [],
                'in_sitemap'    => false,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            [
                'parent'        => 0,
                'is_core'       => true,
                'is_plugin'     => true,
                'is_system'     => true,
                'key'           => 'uploader',
                'slug'          => 'uploader',
                'class_name'    => '',
                'name'          => 'Cms.modules.name.uploader',
                'version'       => $this->version,
                'description'   => 'Cms.modules.description.uploader',
                'extra'         => [],
                'in_sitemap'    => false,
                'active'        => true,
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
        ];

        foreach ($modules as $module) {
            if ($this->MM->insert($module) === false) {
                d($this->MM->errors());
            }
        }

        $subModules = $this->MM->select(['id', 'slug'])->whereIn('slug', ['settings'])->findAll();

        $list = [];

        foreach ($subModules as $subModule) {
            $list[$subModule->slug] = $subModule->id;
        }

        $modules = [
            'settings' => [
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.roles',
                    'slug'          => 'roles',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.roles',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.roles',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.permissions',
                    'slug'          => 'permissions',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.permissions',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.permissions',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.users',
                    'slug'          => 'users',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.users',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.users',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.modules',
                    'slug'          => 'modules',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.modules',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.modules',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.locales',
                    'slug'          => 'locales',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.locales',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.locales',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => false,
                    'key'           => 'settings.seo',
                    'slug'          => 'seo',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.seo',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.seo',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => true,
                    'key'           => 'settings.settings',
                    'slug'          => 'settings',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.settings',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.settings',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => false,
                    'key'           => 'settings.navigations',
                    'slug'          => 'navigations',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.navigations',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.navigations',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
                [
                    'parent'        => $list['settings'],
                    'is_core'       => true,
                    'is_plugin'     => false,
                    'is_system'     => false,
                    'key'           => 'settings.email_template',
                    'slug'          => 'email_template',
                    'class_name'    => '',
                    'name'          => 'Cms.modules.name.email_template',
                    'version'       => $this->version,
                    'description'   => 'Cms.modules.description.email_template',
                    'extra'         => [],
                    'in_sitemap'    => false,
                    'active'        => true,
                    'created_by_id' => $userId,
                    'updated_by_id' => 0,
                ],
            ],
        ];

        foreach ($subModules as $subModule) {
            foreach ($modules as $slug => $list) {
                foreach ($list as $item) {
                    if ($slug === $subModule->slug) {
                        $item['parent'] = $subModule->id;
                        if ($this->MM->insert($item) === false) {
                            d($this->MM->errors());
                        }
                    }
                }
            }
        }
    }

    /**
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
                'is_module'     => true,
                'is_system'     => false,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => false,
                'self'          => false,
                'create'        => false,
                'read'          => false,
                'update'        => false,
                'delete'        => false,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // Default permission System
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => true,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => false,
                'self'          => false,
                'create'        => false,
                'read'          => false,
                'update'        => false,
                'delete'        => false,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // Default permission Plugin
            [
                'role_id'       => 0,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => true,
                'slug'          => '',
                'access'        => false,
                'self'          => false,
                'create'        => false,
                'read'          => false,
                'update'        => false,
                'delete'        => false,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],

            // root Module
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => true,
                'is_system'     => false,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => true,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // root System
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => true,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => true,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // root Plugin
            [
                'role_id'       => 1,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => true,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => true,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],

            // Admin Module
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => true,
                'is_system'     => false,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => true,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // Admin Plugin
            [
                'role_id'       => 2,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => true,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => true,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],

            // Manager Module
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => true,
                'is_system'     => false,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // Manager Plugin
            [
                'role_id'       => 3,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => true,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => true,
                'read'          => true,
                'update'        => true,
                'delete'        => true,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],

            // Default Module
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => false,
                'slug'          => '',
                'access'        => true,
                'self'          => true,
                'create'        => false,
                'read'          => false,
                'update'        => false,
                'delete'        => false,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
            // Default Plugin
            [
                'role_id'       => 4,
                'parent'        => 0,
                'module_id'     => 0,
                'is_module'     => false,
                'is_system'     => false,
                'is_plugin'     => true,
                'slug'          => '',
                'access'        => true,
                'self'          => false,
                'create'        => false,
                'read'          => false,
                'update'        => false,
                'delete'        => false,
                'moderated'     => false,
                'settings'      => false,
                'extra'         => [],
                'created_by_id' => $userId,
                'updated_by_id' => 0,
            ],
        ];

        foreach ($permissions as $permission) {
            if ($this->PM->insert($permission) === false) {
                d($this->PM->errors());
            }
        }

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

                        if ($this->PM->insert($perm) === false) {
                            d($this->PM->errors());
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function _createLocales(int $userId): void
    {
        $locales = [
            [
                'slug'        => 'ru',
                'locale'      => 'ru_RU',
                'locale_name' => 'Русская версия',
                'home'        => 'Главная',
                'extra'       => [
                    'app_name' => 'Мой новый проект',
                    'og:image' => 'ogDefaultRu.jpg',
                ],
                'is_default'    => true,
                'active'        => true,
                'created_by_id' => $userId,
            ],
            [
                'slug'        => 'en',
                'locale'      => 'en_EN',
                'locale_name' => 'English version',
                'home'        => 'Home',
                'extra'       => [
                    'app_name' => 'My new project',
                    'og:image' => 'ogDefaultEn.jpg',
                ],
                'is_default'    => false,
                'active'        => true,
                'created_by_id' => $userId,
            ],
            [
                'slug'        => 'de',
                'locale'      => 'de_DE',
                'locale_name' => 'Deutsche version',
                'home'        => 'Startseite',
                'extra'       => [
                    'app_name' => 'Mein neues Projekt',
                    'og:image' => 'ogDefaultDe.jpg',
                ],
                'is_default'    => false,
                'active'        => true,
                'created_by_id' => $userId,
            ],
        ];

        foreach ($locales as $locale) {
            if ($this->LLM->insert($locale) === false) {
                d($this->LLM->errors());
            }
        }
    }

    /**
     * @throws Exception|ReflectionException
     */
    private function _createSettings(): void
    {
        $settingsList = [
            // .Env
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'timezone',
                'value'         => 'Europe/Moscow',
                'default_value' => 'Europe/Moscow',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.env.timezone',
                'context'       => 'Settings.context.env.timezone',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'secretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.env.secretKey',
                'context'       => 'Settings.context.env.secretKey',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'defLocale',
                'value'         => 'ru',
                'default_value' => 'ru',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.env.defLocale',
                'context'       => 'Settings.context.env.defLocale',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useMultiLocales',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useMultiLocales',
                'context'       => 'Settings.context.env.useMultiLocales',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useFrontend',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useFrontend',
                'context'       => 'Settings.context.env.useFrontend',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'env',
                'key'           => 'useViewData',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.env.useViewData',
                'context'       => 'Settings.context.env.useViewData',
            ],

            // Auth
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useCors',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useCors',
                'context'       => 'Settings.context.auth.useCors',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useSession',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useSession',
                'context'       => 'Settings.context.auth.useSession',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'allowPreRegistration',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.allowPreRegistration',
                'context'       => 'Settings.context.auth.allowPreRegistration',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useToken',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useToken',
                'context'       => 'Settings.context.auth.useToken',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useJwt',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useJwt',
                'context'       => 'Settings.context.auth.useJwt',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSecretKey',
                'value'         => bin2hex(random_bytes(32)),
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.jwtSecretKey',
                'context'       => 'Settings.context.auth.jwtSecretKey',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtSessionsLimit',
                'value'         => 3,
                'default_value' => 3,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtSessionsLimit',
                'context'       => 'Settings.context.auth.jwtSessionsLimit',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtLiveTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtRefreshTime',
                'value'         => 30,
                'default_value' => 30,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.jwtLiveTime',
                'context'       => 'Settings.context.auth.jwtLiveTime',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'jwtAlg',
                'value'         => 'HS256',
                'default_value' => 'HS256',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.jwtAlg',
                'context'       => 'Settings.context.auth.jwtAlg',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useWhiteIpList',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useWhiteIpList',
                'context'       => 'Settings.context.auth.useWhiteIpList',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'whiteIpList',
                'value'         => serialize([]),
                'default_value' => serialize([]),
                'return_type'   => FieldsReturnTypes::Array->value,
                'label'         => 'Settings.label.auth.whiteIpList',
                'context'       => 'Settings.context.auth.whiteIpList',
            ],
            [
                'module_id' => 0,
                'is_core'   => true,
                'entity'    => 'core',
                'slug'      => 'auth',
                'key'       => 'loginTypeList',
                'value'     => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone',
                    ]
                ),
                'default_value' => serialize(
                    [
                        'login',
                        'email',
                        'phone',
                        'login:email',
                        'email:phone',
                        'login:email:phone',
                    ]
                ),
                'return_type' => FieldsReturnTypes::Array->value,
                'label'       => 'Settings.label.auth.loginTypeList',
                'context'     => 'Settings.context.auth.loginTypeList',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'loginType',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.loginType',
                'context'       => 'Settings.context.auth.loginType',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'use2fa',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.use2fa',
                'context'       => 'Settings.context.auth.use2fa',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => '2faField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.2faField',
                'context'       => 'Settings.context.auth.2faField',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeLength',
                'value'         => 4,
                'default_value' => 4,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.verifyCodeLength',
                'context'       => 'Settings.context.auth.verifyCodeLength',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'verifyCodeTime',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.verifyCodeTime',
                'context'       => 'Settings.context.auth.verifyCodeTime',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'useRecovery',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.auth.useRecovery',
                'context'       => 'Settings.context.auth.useRecovery',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryField',
                'value'         => 'email',
                'default_value' => 'email',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.recoveryField',
                'context'       => 'Settings.context.auth.recoveryField',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'recoveryCodeTime',
                'value'         => 10,
                'default_value' => 10,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.recoveryCodeTime',
                'context'       => 'Settings.context.auth.recoveryCodeTime',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'authSmsMessage',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.auth.authSmsMessage',
                'context'       => 'Settings.context.auth.authSmsMessage',
            ],
            [
                // Количество попыток авторизации
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'attempts',
                'value'         => 10,
                'default_value' => 10,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.attempts',
                'context'       => 'Settings.context.auth.attempts',
            ],
            [
                // Время очистки брошенных попыток
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'attemptsClearTime',
                'value'         => 60,
                'default_value' => 60,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.attemptsClearTime',
                'context'       => 'Settings.context.auth.attemptsClearTime',
            ],
            [
                // Время блокировки после неверных попыток авторизации
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'freezeLoginTime',
                'value'         => 60,
                'default_value' => 60,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.auth.freezeLoginTime',
                'context'       => 'Settings.context.auth.freezeLoginTime',
            ],
            [
                // Временные периоды отправки кода SMS регистрации/авторизации
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'codeSmsDelayPeriods',
                'value'         => serialize([5, 15, 30, 45, 60]),
                'default_value' => serialize([5, 15, 30, 45, 60]),
                'return_type'   => FieldsReturnTypes::Array->value,
                'label'         => 'Settings.label.auth.codeSmsDelayTime',
                'context'       => 'Settings.context.auth.codeSmsDelayTime',
            ],
            [
                // Временные периоды отправки кода Email регистрации/авторизации
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'auth',
                'key'           => 'codeEmailDelayPeriods',
                'value'         => serialize([5, 15, 30, 45, 60]),
                'default_value' => serialize([5, 15, 30, 45, 60]),
                'return_type'   => FieldsReturnTypes::Array->value,
                'label'         => 'Settings.label.auth.codeEmailDelayPeriods',
                'context'       => 'Settings.context.auth.codeEmailDelayPeriods',
            ],

            // Seo
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'seo',
                'key'           => 'useSitemap',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.seo.useSitemap',
                'context'       => 'Settings.context.seo.useSitemap',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'seo',
                'key'           => 'allowSiteIndexing',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.seo.allowSiteIndexing',
                'context'       => 'Settings.context.seo.allowSiteIndexing',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'seo',
                'key'           => 'sitemapBatchQty',
                'value'         => 1000,
                'default_value' => 1000,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.seo.sitemapBatchQty',
                'context'       => 'Settings.context.seo.sitemapBatchQty',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'seo',
                'key'           => 'useRobotsTxt',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.seo.useRobotsTxt',
                'context'       => 'Settings.context.seo.useRobotsTxt',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'seo',
                'key'           => 'defRobotsTxt',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.seo.defRobotsTxt',
                'context'       => 'Settings.context.seo.defRobotsTxt',
            ],

            // Email
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'fromEmail',
                'value'         => 'testemail@dvl.to',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.fromEmail',
                'context'       => 'Settings.context.email.fromEmail',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'fromName',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.fromName',
                'context'       => 'Settings.context.email.fromName',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'replyEmail',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.replyEmail',
                'context'       => 'Settings.context.email.replyEmail',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'replyName',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.replyName',
                'context'       => 'Settings.context.email.replyName',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'returnEmail',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.returnEmail',
                'context'       => 'Settings.context.email.returnEmail',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'userAgent',
                'value'         => '*',
                'default_value' => '*',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.userAgent',
                'context'       => 'Settings.context.email.userAgent',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'charset',
                'value'         => 'UTF-8',
                'default_value' => 'UTF-8',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.charset',
                'context'       => 'Settings.context.email.charset',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'mailPath',
                'value'         => '/usr/sbin/sendmail',
                'default_value' => '/usr/sbin/sendmail',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.mailPath',
                'context'       => 'Settings.context.email.mailPath',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'protocol',
                'value'         => 'sendmail',
                'default_value' => 'mail',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.protocol',
                'context'       => 'Settings.context.email.protocol',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'priority',
                'value'         => 1,
                'default_value' => 3,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.priority',
                'context'       => 'Settings.context.email.priority',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'mailType',
                'value'         => 'html',
                'default_value' => 'html',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.mailType',
                'context'       => 'Settings.context.email.mailType',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpHost',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpHost',
                'context'       => 'Settings.context.email.smtpHost',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpUser',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpUser',
                'context'       => 'Settings.context.email.smtpUser',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpPass',
                'value'         => '',
                'default_value' => '',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpPass',
                'context'       => 'Settings.context.email.smtpPass',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpPort',
                'value'         => 465,
                'default_value' => 465,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.smtpPort',
                'context'       => 'Settings.context.email.smtpPort',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpTimeout',
                'value'         => 5,
                'default_value' => 5,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.email.smtpTimeout',
                'context'       => 'Settings.context.email.smtpTimeout',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpKeepalive',
                'value'         => 0,
                'default_value' => 0,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.email.smtpKeepalive',
                'context'       => 'Settings.context.email.smtpKeepalive',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'core',
                'slug'          => 'email',
                'key'           => 'smtpCrypto',
                'value'         => 'ssl',
                'default_value' => 'ssl',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.email.smtpCrypto',
                'context'       => 'Settings.context.email.smtpCrypto',
            ],

            // FileManager
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'field',
                'value'         => 'file',
                'default_value' => 'file',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.filemanager.uploadConfigField',
                'context'       => 'Settings.context.filemanager.uploadConfigField',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'maxSize',
                'value'         => 12,
                'default_value' => 12,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.filemanager.uploadConfigMaxSize',
                'context'       => 'Settings.context.filemanager.uploadConfigMaxSize',
            ],
            [
                'module_id' => 0,
                'is_core'   => true,
                'entity'    => 'filemanager',
                'slug'      => 'uploadConfig',
                'key'       => 'extInImages',
                'value'     => serialize(
                    [
                        'gif',
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                    ]
                ),
                'default_value' => serialize(
                    [
                        'gif',
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                    ]
                ),
                'return_type' => FieldsReturnTypes::Array->value,
                'label'       => 'Settings.label.filemanager.uploadConfigExtInImages',
                'context'     => 'Settings.context.filemanager.uploadConfigExtInImages',
            ],
            [
                'module_id' => 0,
                'is_core'   => true,
                'entity'    => 'filemanager',
                'slug'      => 'uploadConfig',
                'key'       => 'extInFiles',
                'value'     => serialize(
                    [
                        'doc',
                        'rtf',
                        'pdf',
                        'txt',
                        'odt',
                        'odp',
                        'ppsx',
                        'xls',
                        'xlsx',
                        'csv',
                        'ods',
                        'psd',
                        'xml',
                        '7z',
                        '7zip',
                        'rar',
                        'zip',
                    ]
                ),
                'default_value' => serialize(
                    [
                        'doc',
                        'rtf',
                        'pdf',
                        'txt',
                        'odt',
                        'odp',
                        'ppsx',
                        'xls',
                        'xlsx',
                        'csv',
                        'ods',
                        'psd',
                        'xml',
                        '7z',
                        '7zip',
                        'rar',
                        'zip',
                    ]
                ),
                'return_type' => FieldsReturnTypes::Array->value,
                'label'       => 'Settings.label.filemanager.uploadConfigExtInFiles',
                'context'     => 'Settings.context.filemanager.uploadConfigExtInFiles',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'createWebp',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.filemanager.uploadConfigCreateWebp',
                'context'       => 'Settings.context.filemanager.uploadConfigCreateWebp',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'webpQuality',
                'value'         => 90,
                'default_value' => 90,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.filemanager.uploadConfigWebpQuality',
                'context'       => 'Settings.context.filemanager.uploadConfigWebpQuality',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbPrefix',
                'value'         => 'fm_thumb_',
                'default_value' => 'fm_thumb_',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbPrefix',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbPrefix',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbQuality',
                'value'         => 90,
                'default_value' => 90,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbQuality',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbQuality',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbMaintainRatio',
                'value'         => 1,
                'default_value' => 1,
                'return_type'   => FieldsReturnTypes::Boolean->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbMaintainRatio',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbMaintainRatio',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbMasterDim',
                'value'         => 'height',
                'default_value' => 'height',
                'return_type'   => FieldsReturnTypes::String->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbMasterDim',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbMasterDim',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbWidth',
                'value'         => 120,
                'default_value' => 120,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbWidth',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbWidth',
            ],
            [
                'module_id'     => 0,
                'is_core'       => true,
                'entity'        => 'filemanager',
                'slug'          => 'uploadConfig',
                'key'           => 'thumbHeight',
                'value'         => 120,
                'default_value' => 120,
                'return_type'   => FieldsReturnTypes::Integer->value,
                'label'         => 'Settings.label.filemanager.uploadConfigThumbHeight',
                'context'       => 'Settings.context.filemanager.uploadConfigThumbHeight',
            ],
        ];

        foreach ($settingsList as $item) {
            if ($this->SM->insert($item) === false) {
                d($this->SM->errors());
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function _createEmailSystemTemplate(int $userId): void
    {
        $templates = [
            [
                'is_system' => 1,
                'slug'      => 'confirm',
                'label'     => 'Подтверждение email',
                'subject'   => [
                    'ru' => 'Подтверждение email',
                    'en' => 'Email confirmation',
                    'de' => 'Autorisierungscode',
                ],
                'content' => [
                    'ru' => 'Ваш код подтверждения email <b>{{CODE}}</b>.',
                    'en' => 'Your confirmation email code <b>{{CODE}}</b>.',
                    'de' => 'Ihr Zugangswiederherstellungsbestätigungscode ist <b>{{CODE}}</b>.',
                ],
                'view'      => '',
                'variables' => '',
            ],
            [
                'is_system' => 1,
                'slug'      => 'auth',
                'label'     => 'Подтверждение кода авторизации',
                'subject'   => [
                    'ru' => 'Ваш код авторизации',
                    'en' => 'Authorization code',
                    'de' => 'E-Mail-Bestätigung',
                ],
                'content' => [
                    'ru' => 'Ваш код авторизации <b>{{CODE}}</b>.',
                    'en' => 'Your authorization code <b>{{CODE}}</b>.',
                    'de' => 'Ihr Autorisierungscode ist <b>{{CODE}}</b>.',
                ],
                'view'      => '',
                'variables' => '',
            ],
            [
                'is_system' => 1,
                'slug'      => 'recovery',
                'label'     => 'Подтверждение кода восстановления доступов',
                'subject'   => [
                    'ru' => 'Восстановление доступов',
                    'en' => 'Restoring access',
                    'de' => 'Zugriff wiederherstellen',
                ],
                'content' => [
                    'ru' => 'Ваш код подтверждения восстановления доступов <b>{{CODE}}</b>.',
                    'en' => 'Your access recovery confirmation code <b>{{CODE}}</b>.',
                    'de' => 'Ihr Zugangswiederherstellungsbestätigungscode ist <b>{{CODE}}</b>.',
                ],
                'view'      => '',
                'variables' => '',
            ],
        ];

        $module = CmsModule::meta('settings.email_template');

        foreach ($templates as $template) {
            $template['module_id']     = $module['id'];
            $template['created_by_id'] = $userId;
        }

        $this->ETM->insertBatch($templates);
    }

    /**
     * @throws ReflectionException
     */
    private function _setLocales(): void
    {
        if (CLI::prompt('Use multi locales?', ['y', 'n']) === 'y') {
            Cms::settings('core.env.useMultiLocales', 1);
        }
        CLI::newLine();
    }

    private function _createMainPages(): void
    {
        try {
            $useMultiLocales       = Cms::settings('core.env.useMultiLocales');
            $this->contentModuleId = CmsModule::meta('pages')['id'];

            $locales = $this->LLM->where([
                'active' => 1, ...(! $useMultiLocales ? ['is_default' => 1] : []),
            ])->findAll();

            foreach ($locales as $locale) {
                // Создание главной страницы
                $mainId = (new Content(MetaDataTypes::Main->name, $this->contentModuleId))
                    ->createMetaData(
                        [
                            'parent'     => 0,
                            'title'      => $locale->home,
                            'locale_id'  => $locale->id,
                            'slug'       => 'main',
                            'url'        => '/',
                            'page_type'  => 'main',
                            'in_sitemap' => true,
                            'status'     => MetaStatuses::Publish->name,
                        ]
                    );

                // Создание 404 страницы
                (new Content(MetaDataTypes::Page404->name, $this->contentModuleId))
                    ->createMetaData(
                        [
                            'parent'    => $mainId,
                            'title'     => 'Page not found',
                            'slug'      => 'page-404',
                            'url'       => 'page-404',
                            'locale_id' => $locale->id,
                            'page_type' => 'page',
                            'status'    => MetaStatuses::Publish->name,
                        ]
                    );
            }
        } catch (ContentExceptions|ReflectionException $e) {
            dd($e->getMessages() ?? $e->getMessage());
        }

        CLI::newLine();
    }

    /**
     * @throws Exception|ReflectionException
     */
    private function _createPages(): void
    {
        if (CLI::prompt('Create new pages?', ['y', 'n']) === 'y' && ($num = CLI::prompt(
            'How many pages do you want to create?',
            null,
            ['required', 'is_natural']
        )) && ($nesting = CLI::prompt(
            'What is the maximum nesting of pages?',
            null,
            ['required', 'is_natural']
        ))
        ) {
            $useMultiLocales = Cms::settings('core.env.useMultiLocales');

            $locales = $this->LLM->where([
                'active' => 1, ...(! $useMultiLocales ? ['is_default' => 1] : []),
            ])->findAll();

            $num            = (int) $num;
            $this->numPages = $num;
            $mainIds        = $this->MDM->where(['meta_type' => MetaDataTypes::Main->name])->findColumn('id');

            foreach ($locales as $locale) {
                foreach ($mainIds as $id) {
                    $this->_createSubPages(
                        $num,
                        (int) $nesting,
                        $locale->id,
                        $id
                    );
                    break;
                }
            }

            CLI::newLine();
        }
    }

    /**
     * @throws ReflectionException
     */
    private function _createDefaultActions(): void
    {
        Cms::settings('core.seo.defRobotsTxt', view('template/seo/robots.php', [], ['debug' => false]));
    }

    /**
     * @throws ReflectionException|UploaderException
     */
    private function _fileManagerRegistration(): void
    {
        CmsFileManager::createDirectory(
            'content',
            [
                'module_id' => CmsModule::meta('pages')['id'],
            ]
        );

        CmsFileManager::createDirectory(
            'users',
            [
                'module_id' => CmsModule::meta('settings.users')['id'],
            ]
        );
    }

    private function _createPublicFolders(): void
    {
        $directories = [
            'uploads',
            'uploads/content',
            'uploads/users',
            'uploads/sitemaps',
            'uploads/modules',
            'uploads/locales',
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory = FCPATH . $directory)) {
                if (mkdir($directory, 0777, true)) {
                    file_put_contents($directory . '/index.html', '');
                } else {
                    CLI::write('Can\'t create directory: ' . $directory);
                }
            }
        }
    }

    /**
     * @param string|null $url
     *
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
        helper(['date']);

        $meta             = (new Fabricator(MetaDataFactory::class, null))->makeObject();
        $meta->meta_type  = $type;
        $meta->locale_id  = $locale;
        $meta->creator_id = $creator;
        $meta->module_id  = $module;
        $meta->parent     = $parent;
        $meta->item_id    = $item_id;
        $meta->status     = null === $status ? $meta->status : $status;

        switch ($type) {
            case MetaDataTypes::Main->name:
                $meta->url        = '';
                $meta->slug       = 'main';
                $meta->in_sitemap = true;
                $meta->status     = MetaStatuses::Publish->name;
                $meta->sort       = 1;
                $meta->publish_at = date('Y-m-d H:i:s', now());
                break;

            case MetaDataTypes::Page404->name:
                $meta->url        = $meta->slug = 'page-not-found';
                $meta->in_sitemap = false;
                $meta->publish_at = date('Y-m-d H:i:s', now());
                break;
        }

        if ($metaId = $this->MDM->insert($meta)) {
            $content     = (new Fabricator(MetaContentFactory::class, null))->makeObject();
            $content->id = $metaId;
            if ($meta->meta_type === MetaDataTypes::Page404->name) {
                $content->anons = $content->content = '';
            }
            if ($this->CM->insert($content) === false) {
                d($this->CM->errors());
            }
        } else {
            d($this->MDM->errors());
        }

        return $metaId;
    }

    /**
     * @throws ContentExceptions|ReflectionException
     */
    private function _createSubPages(int $num, int $nesting, int $locale, int $parent): void
    {
        if ($num > 0 && $nesting > 0 && ! empty($levels = $this->_getLevelList($num, $nesting))) {
            $pages      = [];
            $contentLib = new Content(MetaDataTypes::Page->name, $this->contentModuleId);

            foreach ($levels as $k => $level) {
                if ($level > 0) {
                    $pages[$k] = [];

                    for ($i = 1; $i <= $level; $i++) {
                        $key = ($k > 1) ? $k - 1 : $k;

                        $data    = (new Fabricator(MetaDataFactory::class, null))->makeObject();
                        $content = (new Fabricator(MetaContentFactory::class, null))->makeObject();

                        $data->parent    = ($k === 1) ? $parent : $pages[$key][array_rand($pages[$key])];
                        $data->locale_id = $locale;
                        $data->anons     = $content->anons;
                        $data->content   = $content->content;

                        unset($data->module_id, $data->meta_type);

                        $pages[$k][] = $contentLib->createMetaData((array) $data);
                    }
                }
            }
        }
    }

    private function _getLevelList(int $totalPages, int $nestedLevels): array
    {
        $distribution = [];
        $pagesArray   = range(1, $totalPages);

        shuffle($pagesArray);

        // Распределяем страницы по уровням
        for ($i = 1; $i <= $nestedLevels; $i++) {
            // Определяем случайное количество страниц для текущего уровня
            $pagesForLevel = $totalPages > 1 ? mt_rand(1, $totalPages) : $totalPages;
            // Записываем случайное количество страниц для текущего уровня в массив
            $distribution[$i] = count(array_splice($pagesArray, 0, $pagesForLevel));
            // Уменьшаем общее количество страниц
            $totalPages -= $pagesForLevel;
        }

        return $distribution;
    }
}
