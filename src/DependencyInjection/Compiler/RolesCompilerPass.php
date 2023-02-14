<?php

/**
 * CoreSystems (c) 2023
 * Author: Josh McCreight<jmccreight@shaw.ca>
 */

declare(strict_types=1);

namespace CoreSys\UserManagement\DependencyInjection\Compiler;

use LogicException;
use RuntimeException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RolesCompilerPass implements CompilerPassInterface
{
    private array $expressions = [];
    private array $requestMatchers = [];

    function process(ContainerBuilder $container)
    {
        // handle setting the roles
        $roleHierarchy = $container->getParameter('coresys.security.role_hierarchy');
        $container->setParameter('security.role_hierarchy.roles', $roleHierarchy);

        $this->processAccessControl($container);
    }

    protected function processAccessControl(ContainerBuilder &$container): void
    {
        $accessControl = $container->getParameter('coresys.security.access_control');

        foreach ($accessControl ??= [] as $access) {
            $access = array_merge([
                'host' => null, 'port' => null, 'ips' => null,
                'methods' => [], 'attributes' => [], 'route' => null,
                'allow_if' => null, 'requires_channel' => null,
                'request_matcher' => null
            ], $access);

            if (isset($access['request_matcher'])) {
                if (
                    $access['path'] || $access['host'] || $access['port'] || $access['ips']
                    || $access['methods'] || $access['attributes'] || $access['route']
                ) {
                    throw new InvalidConfigurationException(
                        'The "request_matcher" option should not be specified alongside other options. ' .
                            'Consider integrating your constraints inside your RequestMatcher directly.'
                    );
                }

                $matcher = new Reference($access['request_matcher'] ??= '');
            } else {
                $attributes = $access['attributes'] ??= [];

                if (!empty($access['route'] ?? null)) {
                    if (array_key_exists('_route', $attributes)) {
                        throw new InvalidConfigurationException(
                            'The "route" option should not be specified alongside "attributes._route" option. ' .
                                'Use just one of the options.'
                        );
                    }
                    $attributes['_route'] = $access['route'];
                }

                $matcher = $this->createRequestMatcher(
                    $container,
                    $access['path'],
                    $access['host'],
                    $access['port'],
                    $access['methods'],
                    $access['ips'],
                    $attributes
                );
            }

            $roles = $access['roles'];
            if ($access['allow_if']) {
                $roles[] = $this->createExpression($container, $access['allow_if']);
            }

            $emptyAccess = 0 === count(array_filter($access));

            if ($emptyAccess) {
                throw new InvalidConfigurationException(
                    'One or more access control items are empty. Did you accidentally add lines only ' .
                        'containing a "-" under "security.access_control"?'
                );
            }

            $container->getDefinition('security.access_map')
                ->addMethodCall('add', [$matcher, $roles, $access['requires_channel']]);
        }

        // allow cache warm-up for expressions
        if (count($this->expressions)) {
            $container->getDefinition('security.cache_warmer.expression')
                ->replaceArgument(0, new IteratorArgument(array_values($this->expressions)));
        } else {
            $container->removeDefinition('security.cache_warmer.expression');
        }
    }

    private function createExpression(ContainerBuilder $container, string $expression): Reference
    {
        if (isset($this->expressions[$id = '.security.expression.' . ContainerBuilder::hash($expression)])) {
            return $this->expressions[$id];
        }

        if (!$container::willBeAvailable('symfony/expression-language', ExpressionLanguage::class, ['symfony/security-bundle'])) {
            throw new RuntimeException(
                'Unable to use expressions as the Symfony ExpressionLanguage component is not installed. ' .
                    'Try running "composer require symfony/expression-language".'
            );
        }

        $container
            ->register($id, Expression::class)
            ->setPublic(false)
            ->addArgument($expression);

        return $this->expressions[$id] = new Reference($id);
    }

    private function createRequestMatcher(
        ContainerBuilder $container,
        string $path = null,
        string $host = null,
        int $port = null,
        array $methods = [],
        array $ips = null,
        array $attributes = []
    ): Reference {
        if ($methods) {
            $methods = array_map('strtoupper', $methods);
        }

        if ($ips) {
            foreach ($ips as $ip) {
                $container->resolveEnvPlaceholders($ip, null, $usedEnvs);

                if (!$usedEnvs && !$this->isValidIps($ip)) {
                    throw new LogicException(
                        sprintf(
                            'The given value "%s" in the "security.access_control" config option is not a valid IP address.',
                            $ip
                        )
                    );
                }

                $usedEnvs = null;
            }
        }

        $id = '.security.request_matcher.' . ContainerBuilder::hash([
            ChainRequestMatcher::class,
            $path, $host, $port, $methods, $ips, $attributes
        ]);

        if (isset($this->requestMatchers[$id])) {
            return $this->requestMatchers[$id];
        }

        $arguments = [];
        if ($methods) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' .
                ContainerBuilder::hash([MethodRequestMatcher::class, $methods]))) {
                $container->register($lid, MethodRequestMatcher::class)->setArguments([$methods]);
            }
            $arguments[] = new Reference($lid);
        }

        if ($path) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' . ContainerBuilder::hash(
                [PathRequestMatcher::class, $path]
            ))) {
                $container->register($lid, PathRequestMatcher::class)->setArguments([$path]);
            }
            $arguments[] = new Reference($lid);
        }

        if ($host) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' . ContainerBuilder::hash([
                HostRequestMatcher::class, $host
            ]))) {
                $container->register($lid, HostRequestMatcher::class)->setArguments([$host]);
            }
            $arguments[] = new Reference($lid);
        }

        if ($ips) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' . ContainerBuilder::hash([
                IpsRequestMatcher::class, $ips
            ]))) {
                $container->register($lid, IpsRequestMatcher::class)->setArguments([$ips]);
            }
            $arguments[] = new Reference($lid);
        }

        if ($attributes) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' . ContainerBuilder::hash([
                AttributesRequestMatcher::class, $attributes
            ]))) {
                $container->register($lid, AttributesRequestMatcher::class)->setArguments([$attributes]);
            }
            $arguments[] = new Reference($lid);
        }

        if ($port) {
            if (!$container->hasDefinition($lid = '.security.request_matcher.' . ContainerBuilder::hash([
                PortRequestMatcher::class, $port
            ]))) {
                $container->register($lid, PortRequestMatcher::class)->setArguments([$port]);
            }
            $arguments[] = new Reference($lid);
        }

        $container
            ->register($id, ChainRequestMatcher::class)
            ->setArguments([$arguments]);

        return $this->requestMatchers[$id] = new Reference($id);
    }

    private function isValidIps(string|array $ips): bool
    {
        $ipsList = array_reduce((array) $ips, static function (array $ips, string $ip) {
            return array_merge($ips, preg_split('/\s*,\s*/', $ip));
        }, []);

        if (!$ipsList) {
            return false;
        }

        foreach ($ipsList as $cidr) {
            if (!$this->isValidIp($cidr)) {
                return false;
            }
        }

        return true;
    }

    private function isValidIp(string $cidr): bool
    {
        $cidrParts = explode('/', $cidr);

        if (1 === count($cidrParts)) {
            return false !== filter_var($cidrParts[0], FILTER_VALIDATE_IP);
        }

        $ip = $cidrParts[0];
        $netmask = $cidrParts[1];

        if (!ctype_digit($netmask)) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $netmask <= 32;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $netmask <= 128;
        }

        return false;
    }
}
