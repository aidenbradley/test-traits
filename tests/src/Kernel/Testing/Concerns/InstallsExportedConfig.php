<?php

namespace Drupal\Tests\test_traits\Kernel\Testing\Concerns;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Tests\test_traits\Kernel\Testing\Exceptions\ConfigInstallFailed;

/** This trait may be used to test fields stored as field configs */
trait InstallsExportedConfig
{
    /** @var array */
    protected $installedConfig = [];

    /** @var bool */
    protected $installFieldModule;

    public function installField(string $fieldName, string $entityType, ?string $bundle = null): void
    {
        if (isset($this->installFieldModule) === false) {
            $this->enableModules(['field']);

            $this->installFieldModule = true;
        }

        $this->installExportedConfig([
            'field.storage.' . $entityType . '.' . $fieldName,
            'field.field.' . $entityType . '.' . ($bundle ? $bundle . '.' : $entityType . '.') . $fieldName,
        ]);
    }

    public function installFields(array $fieldNames, string $entityType, ?string $bundle = null): void
    {
        foreach ($fieldNames as $fieldName) {
            $this->installField($fieldName, $entityType, $bundle);
        }
    }

    public function installImageStyle(string $imageStyle): void
    {
        $this->installExportedConfig([
            'image.style.' . $imageStyle,
        ]);
    }

    public function installImageStyles(array $imageStyles): void
    {
        foreach ($imageStyles as $imageStyle) {
            $this->installImageStyle($imageStyle);
        }
    }

    public function installBundle(string $module, string $bundle): void
    {
        $this->installExportedConfig([
            $module . '.type.' . $bundle,
        ]);
    }

    public function installBundles(string $entityType, array $bundles): void
    {
        foreach ($bundles as $bundle) {
            $this->installBundle($entityType, $bundle);
        }
    }

    /** @param string|array $bundles */
    public function installEntitySchemaWithBundles(string $entityType, $bundles): void
    {
        $this->installEntitySchema($entityType);

        $this->installBundles($entityType, (array)$bundles);
    }

    public function installRole(string $role): void
    {
        $this->installExportedConfig('user.role.' . $role);
    }

    public function installRoles(array $roles): void
    {
        foreach ($roles as $role) {
            $this->installRole($role);
        }
    }

    public function installVocabulary(string $vocabularyName): void
    {
        $this->installExportedConfig([
            'taxonomy.vocabulary.' . $vocabularyName,
        ]);
    }

    public function installVocabularies(array $vocabularies): void
    {
        foreach ($vocabularies as $vocabulary) {
            $this->installVocabulary($vocabulary);
        }
    }

    public function installAllFieldsForEntity(string $entityType, ?string $bundle = null): void
    {
        $configStorage = new FileStorage($this->configDirectory());

        $this->installFields(array_map(function ($storageFieldName) {
            return substr($storageFieldName, strripos($storageFieldName, '.') + 1);
        }, $configStorage->listAll('field.storage.' . $entityType)), $entityType, $bundle);
    }

    /** @param string|array $config */
    public function installExportedConfig($config): void
    {
        $configStorage = new FileStorage($this->configDirectory());

        foreach ((array)$config as $configName) {
            if (in_array($configName, $this->installedConfig)) {
                continue;
            }

            $this->installedConfig[] = $configName;

            $configRecord = $configStorage->read($configName);

            $entityType = $this->container->get('config.manager')->getEntityTypeIdByName($configName);

            /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage */
            $storage = $this->container->get('entity_type.manager')->getStorage($entityType);

            if (is_array($configRecord) === false) {
                throw ConfigInstallFailed::doesNotExist($configName);
            }

            $storage->createFromStorageRecord($configRecord)->save();
        }
    }

    protected function configDirectory(): string
    {
        return Settings::get('config_sync_directory');
    }
}