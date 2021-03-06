<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security\User;

use Contao\Model;
use HeimrichHannot\ApiBundle\Model\ApiAppActionModel;
use HeimrichHannot\ApiBundle\Model\ApiAppModel;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface UserInterface extends AdvancedUserInterface
{
    /**
     * Set current contao member model.
     *
     * @param Model $model
     */
    public function setModel(Model $model);

    /**
     * Get the current contao member model.
     *
     * @return Model
     */
    public function getModel(): Model;

    /**
     * Set login count.
     *
     * @param int $loginCount
     *
     * @return mixed
     */
    public function setLoginCount(int $loginCount);

    /**
     * Get current login count.
     *
     * @return int
     */
    public function getLoginCount(): int;

    /**
     * Set last login time.
     *
     * @param int $lastLogin
     *
     * @return mixed
     */
    public function setLastLogin(int $lastLogin);

    /**
     * Get last login time.
     *
     * @return int
     */
    public function getLastLogin(): int;

    /**
     * Set current login time.
     *
     * @param int $currentLogin
     *
     * @return mixed
     */
    public function setCurrentLogin(int $currentLogin);

    /**
     * Get current login time.
     *
     * @return int
     */
    public function getCurrentLogin(): int;

    /**
     * Find model by value.
     *
     * @param $key
     * @param $value
     *
     * @return UserInterface|null
     */
    public function findBy($key, $value): ?self;

    /**
     * Check if user has access to current app.
     *
     * @param ApiAppModel $model
     *
     * @return bool
     */
    public function hasAppAccess(ApiAppModel $model): bool;

    /**
     * Set active app model.
     *
     * @param ApiAppModel $model
     *
     * @return mixed
     */
    public function setApp(ApiAppModel $model);

    /**
     * Get active app model.
     *
     * @return ApiAppModel|null
     */
    public function getApp(): ?ApiAppModel;

    /**
     * Set active app action model.
     *
     * @param ApiAppModel $model
     *
     * @return mixed
     */
    public function setAppAction(ApiAppActionModel $model);

    /**
     * Get active app action model.
     *
     * @return ApiAppModel|null
     */
    public function getAppAction(): ?ApiAppActionModel;

    /**
     * Get the model table.
     *
     * @return string
     */
    public function getModelTable(): string;
}
