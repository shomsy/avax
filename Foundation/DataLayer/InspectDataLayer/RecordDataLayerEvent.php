<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class RecordDataLayerEvent
{
    public function __construct(
        private DataLayerEvent $event
    ) {}

    public function describeResponsibility() : string
    {
        return 'records data layer event to event store.';
    }

    public function record(DataLayerEventType $type, array $metadata = []) : DataLayerEvent
    {
        return DataLayerEvent::create($type, $metadata);
    }

    public function toMetadata() : array
    {
        return [];
    }
}