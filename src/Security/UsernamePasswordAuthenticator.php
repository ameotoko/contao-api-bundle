<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ApiBundle\Security;

use Contao\Config;
use Contao\System;
use Contao\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UsernamePasswordAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $this->translator->setLocale($request->getDefaultLocale());

        if ('POST' !== $request->getMethod()) {
            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.post_method_only'));
        }

        return [
            'username' => $request->getUser() ?: $request->request->get('username'),
            'password' => $request->getPassword() ?: $request->request->get('password'),
            'entity' => $request->attributes->get('_entity'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials);
    }

    /**
     * {@inheritdoc}
     *
     * @var \HeimrichHannot\ApiBundle\Security\User\UserInterface
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $time = time();

        // TODO: move it to the hook below for BC
        $encoder = $this->encoderFactory->getEncoder(User::class);
        $authenticated = $encoder->isPasswordValid($user->getPassword(), $credentials['password'], $user->getSalt());

        // HOOK: pass credentials to callback functions
        if (!$authenticated && isset($GLOBALS['TL_HOOKS']['checkCredentials']) && \is_array($GLOBALS['TL_HOOKS']['checkCredentials'])) {
            /** @var System $system */
            $system = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_HOOKS']['checkCredentials'] as $callback) {
                $authenticated = $system->importStatic($callback[0], 'auth', true)->{$callback[1]}($credentials['username'], $credentials['password'], $user);

                // Authentication successfull
                if (true === $authenticated) {
                    break;
                }
            }
        }

        if (!$authenticated) {
            $user->setLoginCount($user->getLoginCount() + 1);
            $user->getModel()->save();

            throw new AuthenticationException($this->translator->trans('huh.api.exception.auth.invalid_credentials'));
        }

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $user->setLastLogin($user->getCurrentLogin());
        $user->setCurrentLogin($time);
        $user->setLoginCount(0);
        $user->getModel()->save();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        if (\in_array($request->attributes->get('_scope'), ['api_login_user', 'api_login_member'])) {
            return true;
        }

        return false;
    }
}
