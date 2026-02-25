<?php

namespace d3system\compnents;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;

class D3Flag extends Component
{
    /** @var array<string, mixed> Static flags from config (default backend) */
    public array $flags = [];

    /** @var string|null cache component ID (e.g. "cache") */
    public ?string $cacheId = null;

    /** @var int cache TTL in seconds */
    public int $cacheTtl = 30;

    /**
     * Check whether a flag is enabled.
     *
     * Supported flag values:
     * - bool: true/false
     * - array: ["enabled" => true, "users" => [1,2], "roles" => ["admin"], "percent" => 20]
     * @throws InvalidConfigException
     */
    public function enabled(string $name, array $context = []): bool
    {
        $definition = $this->getFlagDefinition($name);

        if ($definition === null) {
            return false;
        }

        // simplest form
        if (is_bool($definition)) {
            return $definition;
        }

        if (!is_array($definition)) {
            return false;
        }

        $enabled = (bool)($definition['enabled'] ?? false);
        if (!$enabled) {
            return false;
        }

        // user allowlist
        if (isset($definition['users']) && is_array($definition['users'])) {
            $userId = $context['userId'] ?? $this->resolveUserId();
            if ($userId === null || !in_array((int)$userId, array_map('intval', $definition['users']), true)) {
                return false;
            }
        }

        // role allowlist (RBAC)
        if (isset($definition['roles']) && is_array($definition['roles'])) {
            $roles = $definition['roles'];
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                foreach ($roles as $role) {
                    if (Yii::$app->user->can($role)) {
                        return true; // role matched and already enabled
                    }
                }
                // role filter present but none matched
                return false;
            }
            return false;
        }

        // percentage rollout (stable-ish by userId or provided key)
        if (isset($definition['percent'])) {
            $percent = (int)$definition['percent'];
            $percent = max(0, min(100, $percent));

            $key = $context['rolloutKey'] ?? ($context['userId'] ?? $this->resolveUserId());
            if ($key === null) {
                return false;
            }

            return $this->bucket($name, (string)$key) < $percent;
        }

        return true;
    }

    /**
     * Optional: fetch the raw definition (for debugging/admin pages).
     * @throws InvalidConfigException
     */
    public function definition(string $name)
    {
        return $this->getFlagDefinition($name);
    }

    // -----------------------
    // Internals
    // -----------------------

    /**
     * @throws InvalidConfigException
     */
    private function getFlagDefinition(string $name)
    {
        $cache = $this->cache();

        $cacheKey = ['flags', 'definition', $name];
        if ($cache) {
            $cached = $cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Backend #1: config array (simple, fast)
        $definition = $this->flags[$name] ?? null;

        // Backend #2 (optional): if you later add DB storage, you can merge/override here
        // $definition = $this->loadFromDb($name) ?? $definition;

        if ($cache) {
            $cache->set($cacheKey, $definition, $this->cacheTtl);
        }

        return $definition;
    }

    /**
     * @throws InvalidConfigException
     */
    private function cache(): ?CacheInterface
    {
        if (!Yii::$app->has($this->cacheId)) {
            return null;
        }
        $cache = Yii::$app->get($this->cacheId);
        return ($cache instanceof CacheInterface) ? $cache : null;
    }

    private function resolveUserId(): ?int
    {
        if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
            return (int)Yii::$app->user->id;
        }
        return null;
    }

    /**
     * Deterministic bucket in [0..99] based on flag name and key.
     */
    private function bucket(string $flagName, string $key): int
    {
        $hash = crc32($flagName . ':' . $key);
        return $hash % 100;
    }
}