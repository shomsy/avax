<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

final readonly class RequireStrongConsistency
{
    private ConsistencyModel $model;

    public function __construct()
    {
        $this->model = ConsistencyModel::strong();
    }

    public function describeResponsibility() : string
    {
        return 'requires strong consistency by configuring the consistency model.';
    }

    public function enforce() : ConsistencyModel
    {
        return $this->model;
    }

    public function shouldWaitForQuorum(QuorumPolicy $quorum) : bool
    {
        return $quorum->strict;
    }

    public function toMetadata() : array
    {
        return $this->model->toMetadata();
    }
}