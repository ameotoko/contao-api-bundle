<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\ApiBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use HeimrichHannot\ApiBundle\ContaoApiBundle;
use HeimrichHannot\ApiBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class Plugin implements BundlePluginInterface, RoutingPluginInterface, ExtensionPluginInterface
{

    /**
     * Gets a list of autoload configurations for this bundle.
     *
     * @param ParserInterface $parser
     *
     * @return ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoApiBundle::class)->setLoadAfter(
                [
                    ContaoCoreBundle::class,
                    'privacy',
                ]
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = '@ContaoApiBundle/Resources/config/routing.yml';

        return $resolver->resolve($file)->load($file);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if ('security' === $extensionName) {

            $extensionConfigs = $this->getSecurityExtensionConfig($extensionConfigs, $container);

            return $extensionConfigs;
        }

        return $extensionConfigs;
    }

    /**
     * Get security extension config
     *
     * @param array            $extensionConfigs
     * @param ContainerBuilder $container
     *
     * @return array
     */
    public function getSecurityExtensionConfig(array $extensionConfigs, ContainerBuilder $container)
    {
        $firewalls = [
            'api_login_member' => [
                'request_matcher' => 'huh.api.routing.login.member.matcher',
                'stateless'       => true,
                'guard'           => [
                    'authenticators' => ['huh.api.security.username_password_authenticator'],
                ],
                'provider'        => 'huh.api.security.user_provider',
            ],
            'api_login_user' => [
                'request_matcher' => 'huh.api.routing.login.user.matcher',
                'stateless'       => true,
                'guard'           => [
                    'authenticators' => ['huh.api.security.username_password_authenticator'],
                ],
                'provider'        => 'huh.api.security.user_provider',
            ],
            'api'       => [
                'request_matcher' => 'huh.api.routing.matcher',
                'stateless'       => true,
                'guard'           => [
                    'authenticators' => ['huh.api.security.token_authenticator'],
                ],
                'provider'        => 'huh.api.security.user_provider',
            ],
        ];

        $providers = [
            'huh.api.security.user_provider' => [
                'id' => 'huh.api.security.user_provider',
            ],
        ];

        foreach ($extensionConfigs as &$extensionConfig) {
            $extensionConfig['firewalls'] = (isset($extensionConfig['firewalls']) && is_array($extensionConfig['firewalls']) ? $extensionConfig['firewalls'] : []) + $firewalls;
            $extensionConfig['providers'] = (isset($extensionConfig['providers']) && is_array($extensionConfig['providers']) ? $extensionConfig['providers'] : []) + $providers;
        }

        return $extensionConfigs;
    }
}