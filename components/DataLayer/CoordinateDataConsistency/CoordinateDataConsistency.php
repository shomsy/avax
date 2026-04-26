<?php

declare(strict_types=1);

namespace components\DataLayer\CoordinateDataConsistency;

final readonly class CoordinateDataConsistency
{
    public function __construct(
        private ConsistencyModel $model,
        private QuorumPolicy     $quorum,
        private CapTradeoff      $capTradeoff
    ) {}

    public function describeResponsibility() : string
    {
        return 'coordinates data consistency using consistency model, quorum, and CAP tradeoff.';
    }

    public function getConsistencyModel() : ConsistencyModelType
    {
        return $this->model->type;
    }

    public function getQuorum() : QuorumPolicy
    {
        return $this->quorum;
    }

    public function getCapTradeoff() : CapPreference
    {
        return $this->capTradeoff->preference;
    }

    public function shouldRequireStrongConsistency() : bool
    {
        return $this->model->type === ConsistencyModelType::STRONG;
    }

    public function toMetadata() : array
    {
        return [
            'consistency_model' => $this->model->toMetadata(),
            'quorum'            => $this->quorum->toMetadata(),
            'cap_tradeoff'      => $this->capTradeoff->toMetadata(),
        ];
    }
}