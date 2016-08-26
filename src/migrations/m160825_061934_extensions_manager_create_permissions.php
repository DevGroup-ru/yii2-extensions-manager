<?php

use yii\db\Migration;

class m160825_061934_extensions_manager_create_permissions extends Migration
{
    const ADMIN_ROLE_NAME = 'ExtensionsManagerAdministrator';

    protected $permissions = [
        'extensions-manager-view-extensions' => 'You can see extensions list, extension details and you search',
        'extensions-manager-install-extension' => 'You can install extensions',
        'extensions-manager-uninstall-extension' => 'You can uninstall non-core extensions',
        'extensions-manager-activate-extension' => 'You can activate non-core extensions',
        'extensions-manager-deactivate-extension' => 'You can deactivate non-core extensions',
        'extensions-manager-configure-extension' => 'You can configure non-core extensions',
        'extensions-manager-access-to-core-extension' => 'You have access to managing of core extensions except installing and uninstalling',
    ];

    protected function error($message)
    {
        $length = strlen($message);
        echo "\n" . str_repeat('=', $length) . "\n" . $message . "\n" . str_repeat('=', $length) . "\n\n";
    }

    public function up()
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            $this->error('Please configure AuthManager before');
            return false;
        }
        try {
            $role = $auth->createRole(self::ADMIN_ROLE_NAME);
            $role->description = 'This role allows to manage an extensions-manager';
            $auth->add($role);
            foreach ($this->permissions as $name => $description) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
                $auth->addChild($role, $permission);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return false;
        }
        return true;
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        if ($auth !== null) {
            $role = $auth->getRole(self::ADMIN_ROLE_NAME);
            if ($role !== null) {
                $auth->remove($role);
                foreach ($this->permissions as $name => $description) {
                    $permission = $auth->getPermission($name);
                    if ($permission === null) {
                        continue;
                    }
                    $auth->remove($permission);
                }
            }
        }
    }
}
