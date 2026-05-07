<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\PublicSurface;

/**
 * SystemDesignKit — V3 Executable System Design Framework.
 *
 * Status: @experimental
 * Placement: labs/ (not promoted to components/ yet)
 *
 * Purpose: model, validate, simulate, test, and explain
 * large application architectures.
 *
 * V3 does not just build applications.
 * V3 tests whether the architecture makes sense.
 */
final class SystemDesignKit
{
    /**
     * @param array<string, mixed> $config
     *
     * @return array{valid: bool, errors: list<string>}
     */
    public static function validateCapacity(array $config) : array
    {
        $errors = [];

        if (! isset($config['traffic'])) {
            $errors[] = 'Missing traffic model.';
        } else {
            $traffic = $config['traffic'];
            if (! isset($traffic['requests_per_second']) || ! is_int($traffic['requests_per_second'])) {
                $errors[] = 'traffic.requests_per_second must be an integer.';
            }
            if (! isset($traffic['read_write_ratio']) || ! is_int($traffic['read_write_ratio'])) {
                $errors[] = 'traffic.read_write_ratio must be an integer.';
            }
        }

        if (! isset($config['storage'])) {
            $errors[] = 'Missing storage growth model.';
        }

        if (! isset($config['cache'])) {
            $errors[] = 'Missing cache strategy model.';
        } else {
            $cache = $config['cache'];
            if (isset($cache['hit_ratio_target'])) {
                $ratio = $cache['hit_ratio_target'];
                if ($ratio < 0 || $ratio > 1) {
                    $errors[] = 'cache.hit_ratio_target must be between 0 and 1.';
                }
            }
        }

        if (! isset($config['queue'])) {
            $errors[] = 'Missing queue pressure model.';
        }

        if (! isset($config['latency'])) {
            $errors[] = 'Missing latency budget model.';
        }

        if (! isset($config['availability'])) {
            $errors[] = 'Missing availability model.';
        } else {
            $avail = $config['availability'];
            if (isset($avail['slo'])) {
                $slo = $avail['slo'];
                if ($slo < 0 || $slo > 100) {
                    $errors[] = 'availability.slo must be between 0 and 100.';
                }
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
