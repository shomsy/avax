<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class DescribeFailoverPlan
{
    public function __construct(
        public FailoverPlan $plan,
        public string       $description,
        public array        $steps
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes failover plan including steps, endpoints, and verification.';
    }

    public static function standard() : self
    {
        $plan = FailoverPlan::standard();

        return new self(
            plan       : $plan,
            description: 'Automatic failover to replica when primary fails health checks.',
            steps      : ['detect_failure', 'promote_replica', 'update_routing', 'verify_health']
        );
    }

    public function toMetadata() : array
    {
        return [
            'plan'        => $this->plan->toMetadata(),
            'description' => $this->description,
            'steps'       => $this->steps,
        ];
    }
}