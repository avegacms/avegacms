<?php

declare(strict_types = 1);

namespace AvegaCms\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
use AvegaCms\Enums\{
    UserStatuses,
    UserConditions,
    FieldsReturnTypes,
    FileTypes,
    MetaStatuses,
    NavigationTypes,
    MetaDataTypes
};
use AvegaCms\Utilities\Migrator;

class CreateAvegaCmsTables extends Migration
{
    private array $attributes;

    private array $tables = [
        'users'           => 'users',
        'roles'           => 'roles',
        'user_roles'      => 'user_roles',
        'user_tokens'     => 'user_tokens',
        'locales'         => 'locales',
        'modules'         => 'modules',
        'settings'        => 'settings',
        'metadata'        => 'metadata',
        'content'         => 'content',
        'tags'            => 'tags',
        'tags_links'      => 'tags_links',
        'files'           => 'files',
        'files_links'     => 'files_links',
        'sessions'        => 'sessions',
        'permissions'     => 'permissions',
        'navigations'     => 'navigations',
        'email_templates' => 'email_templates',
    ];

    /**
     * @return void
     */
    public function up(): void
    {
        $this->attributes = ($this->db->getPlatform() === 'MySQLi') ? Migrator::$attributes : [];

        /**
         * Таблица пользователей
         */
        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'login'       => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'avatar'      => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
            'phone'       => ['type' => 'decimal', 'constraint' => 11, 'null' => true],
            'email'       => ['type' => 'varchar', 'constraint' => 255, 'unique' => true, 'null' => true],
            'timezone'    => ['type' => 'varchar', 'constraint' => 144, 'default' => 'Europe/Moscow'],
            'password'    => ['type' => 'varchar', 'constraint' => 255],
            'secret'      => ['type' => 'varchar', 'constraint' => 255],
            'path'        => ['type' => 'varchar', 'constraint' => 512], // Будет храниться хэш
            'expires'     => ['type' => 'int', 'null' => true, 'default' => 0], // Срок действия хэша
            'is_verified' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => 0],
            'profile'     => ['type' => 'text', 'null' => true],
            'extra'       => ['type' => 'text', 'null' => true],
            // Дополнительные поля
            'status'      => [
                'type'       => 'enum',
                'constraint' => UserStatuses::get('value'),
                'default'    => UserStatuses::NotDefined->value
            ],
            'condition'   => [
                'type'       => 'enum',
                'constraint' => UserConditions::get('value'),
                'default'    => UserConditions::None->value
            ],
            'last_ip'     => ['type' => 'varchar', 'constraint' => 45],
            'last_agent'  => ['type' => 'varchar', 'constraint' => 512],
            'active_at'   => ['type' => 'datetime', 'null' => true],
            ...Migrator::byId(),
            ...Migrator::dateFields()
        ]);
        $this->forge->addPrimaryKey('id');
        $this->createTable($this->tables['users']);

        /**
         * Таблица ролей
         */
        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'role'        => ['type' => 'varchar', 'constraint' => 36, 'unique' => true, 'null' => true],
            'description' => ['type' => 'text', 'constraint' => 512, 'null' => true],
            'color'       => ['type' => 'varchar', 'constraint' => 16, 'null' => true],
            'path'        => ['type' => 'varchar', 'constraint' => 512, 'null' => true],
            // Приоритет роли, в случае, если будут одинаковые действия
            'priority'    => ['type' => 'tinyint', 'constraint' => 3, 'null' => 0, 'default' => 0],
            // Роль имеет свой отдельный доступ
            'self_auth'   => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // ID модуля
            'module_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            // Сущность для профиля роли
            'role_entity' => ['type' => 'varchar', 'constraint' => 512, 'null' => true],
            'active'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->createTable($this->tables['roles']);

        /**
         * Таблица связки ролей и пользователей
         */
        $this->forge->addField([
            'role_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by_id' => ['type' => 'int', 'constraint' => 11, 'null' => true, 'default' => 0],
            ...Migrator::dateFields(['updated_at', 'deleted_at'])
        ]);
        $this->forge->addUniqueKey(['user_id', 'role_id']);
        $this->forge->addForeignKey('role_id', $this->tables['roles'], 'id', onDelete: 'CASCADE');
        $this->forge->addForeignKey('user_id', $this->tables['users'], 'id', onDelete: 'CASCADE');
        $this->createTable($this->tables['user_roles']);

        /**
         * Таблица пользовательских токенов
         */
        $this->forge->addField([
            'id'            => ['type' => 'varchar', 'constraint' => 128, 'unique' => true, 'null' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'access_token'  => ['type' => 'varchar', 'constraint' => 2048],
            'refresh_token' => ['type' => 'varchar', 'constraint' => 64, 'unique' => true],
            'expires'       => ['type' => 'int', 'null' => true, 'default' => 0],
            'user_ip'       => ['type' => 'varchar', 'constraint' => 255],
            'user_agent'    => ['type' => 'varchar', 'constraint' => 512],
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addForeignKey('user_id', $this->tables['users'], 'id', onDelete: 'CASCADE');
        $this->createTable($this->tables['user_tokens']);

        /**
         * Таблица расширений для модулей приложения
         */
        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'is_core'     => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'is_system'   => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'is_plugin'   => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'slug'        => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            'class_name'  => ['type' => 'varchar', 'constraint' => 128, 'null' => true],
            'key'         => ['type' => 'varchar', 'constraint' => 255, 'unique' => true],
            'name'        => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'version'     => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            'description' => ['type' => 'text', 'null' => true],
            'extra'       => ['type' => 'text', 'null' => true],
            'url_pattern' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'in_sitemap'  => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'active'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['parent', 'is_core', 'key']);
        $this->createTable($this->tables['modules']);

        /**
         * Таблица "местоположения" используется как для мультязычных, так и для мультирегиональных приложений/сайтов
         */
        $this->forge->addField([
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'slug'        => ['type' => 'varchar', 'constraint' => 20, 'unique' => true, 'null' => true],
            // Значение, которое будет отображаться в ULR (пример: ru / omsk)
            'locale'      => ['type' => 'varchar', 'constraint' => 32, 'null' => true],
            // Для SEO (пример: ru_RU / en_EN)
            'locale_name' => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            // Наименование локали (пример: Русский язык / English language / omsk)
            'home'        => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            // Для SEO начальное значение для breadcrumbs
            'extra'       => ['type' => 'text', 'null' => true],
            // Дополнительны данные
            'is_default'  => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'active'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->createTable($this->tables['locales']);

        /**
         * Таблица настроек, где будут храниться настройки приложения
         */
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'locale_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'module_id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'is_core'       => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'entity'        => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'slug'          => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'key'           => ['type' => 'varchar', 'constraint' => 36, 'null' => true],
            'value'         => ['type' => 'text', 'null' => true],
            'default_value' => ['type' => 'text', 'null' => true],
            'return_type'   => [
                'type'       => 'enum',
                'constraint' => FieldsReturnTypes::get('value'),
                'default'    => FieldsReturnTypes::String->value
            ],
            'label'         => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'context'       => ['type' => 'varchar', 'constraint' => 512, 'null' => true],
            'sort'          => ['type' => 'tinyint', 'constraint' => 3, 'null' => 0, 'default' => 100],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['locale_id', 'module_id', 'entity', 'slug', 'key']);
        $this->createTable($this->tables['settings']);

        /**
         * Таблица "местоположения" используется как для мультязычных, так и для мультирегиональных приложений/сайтов
         */
        $this->forge->addField([
            'id'       => [
                'type'           => 'bigint',
                'constraint'     => 16,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'data'     => ['type' => 'text', 'null' => true],
            'extra'    => ['type' => 'text', 'null' => true],
            'provider' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'type'     => [
                'type'       => 'enum',
                'constraint' => FileTypes::get('value'),
                'default'    => FileTypes::File->value
            ],
            'active'   => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->createTable($this->tables['files']);

        /**
         * Таблица связка файлов и модулей
         */
        $this->forge->addField(
            [
                'id'        => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true],
                'user_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
                // id - родительского модуля
                'parent'    => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
                // id - модуль
                'module_id' => [
                    'type'    => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true,
                    'default' => 0
                ],
                // id - сущности элемента модуля
                'entity_id' => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
                // id - элемента сущности
                'item_id'   => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
                // принадлежность к модулю
                'uid'       => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
                'active'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
                'type'      => [
                    'type'       => 'enum',
                    'constraint' => FileTypes::get('value'),
                    'default'    => FileTypes::File->value
                ],
                ...Migrator::byId(),
                ...Migrator::dateFields(['deleted_at'])
            ]
        );
        $this->forge->addUniqueKey(['id', 'user_id', 'parent', 'module_id', 'entity_id', 'item_id']);
        $this->forge->addForeignKey('id', $this->tables['files'], 'id', onDelete: 'CASCADE');
        $this->createTable($this->tables['files_links']);

        /**
         * Таблица для хранения пользовательских сессий
         */
        $this->forge->addField([
            'id'         => ['type' => 'varchar', 'constraint' => 128, 'null' => false],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'ip_address' => ['type' => 'varchar', 'constraint' => 45, 'null' => false],
            'timestamp'  => ['type' => 'timestamp', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'data'       => ['type' => 'blob', 'null' => false]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('timestamp');
        $this->createTable($this->tables['sessions']);

        /**
         * Таблица для хранения SEO-данных страниц приложения
         */
        $this->forge->addField([
            'id'              => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'auto_increment' => true],
            'parent'          => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
            // id - родительской записи
            'locale_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0],
            // принадлежность к локалии
            'module_id'       => [
                'type'    => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true,
                'default' => 0
            ],
            // принадлежность к модулю
            'slug'            => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            // принадлежность к элементу модуля
            'creator_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            // id - пользователя создавшего запись
            'item_id'         => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
            // id - файла превью записи
            'preview_id'      => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'default' => 0],
            // id - элемента записи
            'title'           => ['type' => 'varchar', 'constraint' => 1024, 'null' => true],
            // Название страницы
            'sort'            => [
                'type'    => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true,
                'default' => 0
            ],
            // порядковый номер записи
            'url'             => ['type' => 'varchar', 'constraint' => 2048, 'null' => true],
            // URL-адрес без указания base_url
            'meta'            => ['type' => 'text', 'null' => true],
            // объект, содержащий информацию о метаданных
            'extra_data'      => ['type' => 'text', 'null' => true],
            // объект, содержащий информацию о дополнительных данных
            'status'          => [
                'type'       => 'enum',
                'constraint' => MetaStatuses::get('name'),
                'default'    => MetaStatuses::Publish->name
            ],
            // Матасущность страницы
            'meta_type'       => [
                'type'       => 'enum',
                'constraint' => MetaDataTypes::get('name'),
                'default'    => MetaDataTypes::Undefined->name
            ],
            'page_type'       => ['type' => 'varchar', 'constraint' => 64, 'null' => true, 'default' => ''],
            // флаг добавления в карту сайта
            'in_sitemap'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // Метаданные для карты сайта
            'meta_sitemap'    => ['type' => 'text', 'null' => true],
            // флаг использования шаблона url
            'use_url_pattern' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'publish_at'      => ['type' => 'datetime', 'null' => true],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['parent', 'locale_id', 'module_id', 'item_id', 'use_url_pattern', 'slug']);
        $this->createTable($this->tables['metadata']);

        /**
         * Таблица для хранения страниц
         */
        $this->forge->addField([
            'id'      => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true],
            'anons'   => ['type' => 'text', 'null' => true], // краткая информация
            'content' => ['type' => 'longtext', 'null' => true], // остальная информация
            'extra'   => ['type' => 'longtext', 'null' => true] // объект, содержащий информацию о дополнительных данных
        ]);
        $this->forge->addUniqueKey(['id']);
        $this->createTable($this->tables['content']);

        /**
         * Таблица с тегами
         */
        $this->forge->addField([
            'id'     => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'   => ['type' => 'varchar', 'constraint' => 128, 'null' => true],
            'slug'   => ['type' => 'varchar', 'constraint' => 64, 'unique' => true, 'null' => true],
            'active' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('created_by_id', $this->tables['users'], 'id', onDelete: 'SET DEFAULT');
        $this->forge->addForeignKey('updated_by_id', $this->tables['users'], 'id', onDelete: 'SET DEFAULT');
        $this->createTable($this->tables['tags']);

        /**
         * Таблица связи тегов
         */
        $this->forge->addField([
            'tag_id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'meta_id'       => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true],
            'created_by_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0, 'default' => 0],
            ...Migrator::dateFields(['updated_at', 'deleted_at'])
        ]);
        $this->forge->addUniqueKey(['tag_id', 'meta_id']);
        $this->forge->addForeignKey('tag_id', $this->tables['tags'], 'id', onDelete: 'CASCADE');
        $this->forge->addForeignKey('meta_id', $this->tables['metadata'], 'id', onDelete: 'CASCADE');
        $this->forge->addForeignKey('created_by_id', $this->tables['users'], 'id', onDelete: 'SET DEFAULT');
        $this->createTable($this->tables['tags_links']);

        $this->forge->addField([
            'id'        => ['type' => 'bigint', 'constraint' => 16, 'unsigned' => true, 'auto_increment' => true],
            'role_id'   => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'module_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => 0],
            // Является ли запись модулем
            'is_module' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // Является ли запись системной
            'is_system' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // Является ли модуль плагином
            'is_plugin' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // принадлежность к модулю
            'parent'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            // URL-slug модуля
            'slug'      => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            // разрешение на просмотр
            'access'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // действия разрешены только со своими записями
            'self'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // разрешение на создание
            'create'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // разрешение на создание
            'read'      => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // разрешение на чтение
            'update'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // разрешение на обновление/редактирование
            'delete'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // разрешение на удаление
            'moderated' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            // действие требует модерации вышестоящих
            'settings'  => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'extra'     => ['type' => 'text', 'null' => true], // Настройки для плагинов
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['role_id', 'module_id', 'is_module', 'is_system', 'is_plugin', 'parent', 'slug']);
        $this->createTable($this->tables['permissions']);

        $this->forge->addField([
            'id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'is_admin'  => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'object_id' => ['type' => 'smallint', 'constraint' => 6, 'unsigned' => true, 'default' => 0],
            'locale_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0],
            'nav_type'  => [
                'type'       => 'enum',
                'constraint' => NavigationTypes::get('value'),
                'default'    => NavigationTypes::Link->value
            ],
            'meta'      => ['type' => 'text', 'null' => true],
            'title'     => ['type' => 'varchar', 'constraint' => 512, 'null' => true],
            'slug'      => ['type' => 'varchar', 'constraint' => 512, 'null' => true],
            'icon'      => ['type' => 'varchar', 'constraint' => 512, 'null' => true, 'default' => ''],
            'sort'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => 0],
            'active'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['parent', 'is_admin', 'locale_id', 'nav_type', 'slug']);
        $this->createTable($this->tables['navigations']);

        $this->forge->addField([
            'id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'module_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => 0],
            'is_system' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'label'     => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'slug'      => ['type' => 'varchar', 'constraint' => 64, 'null' => true],
            'subject'   => ['type' => 'text', 'null' => true],
            'content'   => ['type' => 'text', 'null' => true],// Если нет шаблона, то код можно писать сюда
            'variables' => ['type' => 'text', 'null' => true],
            'view'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'active'    => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
            ...Migrator::byId(),
            ...Migrator::dateFields(['deleted_at'])
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['module_id', 'is_system', 'slug']);
        $this->forge->addForeignKey('module_id', $this->tables['modules'], 'id', onDelete: 'CASCADE');
        $this->createTable($this->tables['email_templates']);
    }

    /**
     * @return void
     */
    public function down(): void
    {
        $this->db->disableForeignKeyChecks();

        foreach ($this->tables as $table) {
            $this->forge->dropTable($table, true);
        }

        $this->db->enableForeignKeyChecks();
    }

    /**
     * @param  string  $tableName
     * @return void
     */
    private function createTable(string $tableName): void
    {
        $this->forge->createTable($tableName, false, $this->attributes);
    }
}
