<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

trait InteractsWithAuthentication
{
    /** @var UserInterface */
    private $anonymousUser;

    /** @param UserInterface|RoleInterface */
    public function actingAs($user): self
    {
        if ($user instanceof RoleInterface) {
            return $this->actingAsRole($user);
        }

        $this->container->get('current_user')->setAccount($user);

        return $this;
    }

    public function actingAsAnonymous(): self
    {
        if (isset($this->anonymousUser) === false) {
            $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

            $userStorage->create([
                'uid' => 0,
                'name' => 'anonymous',
                'status' => 1,
            ])->save();

            $this->anonymousUser = $userStorage->load(0);
        }

        $this->container->get('current_user')->setAccount($this->anonymousUser);

        return $this;
    }

    public function actingAsRole(RoleInterface $role): self
    {
        $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

        $user = $userStorage->create([
            'name' => $role->id(),
        ]);
        $user->addRole($role);
        $user->save();

        return $this->actingAs($user);
    }
}
