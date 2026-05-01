<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Models;

/**
 * PreCommit Issue Model
 *
 * Represents a detected issue in the codebase.
 */
final class PreCommitIssue
{
    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_ERROR    = 'error';
    public const SEVERITY_WARNING  = 'warning';
    public const SEVERITY_INFO     = 'info';

    public const DELETE_SAFE            = 'safe_to_delete';
    public const DELETE_PROBABLY_SAFE   = 'probably_safe_but_requires_review';
    public const DELETE_UNSAFE          = 'unsafe_to_delete';
    public const DELETE_KEEP_PUBLIC_API = 'keep_because_public_api';
    public const DELETE_KEEP_COMPAT     = 'keep_because_compatibility_contract';
    public const DELETE_KEEP_REFERENCED = 'keep_because_referenced';

    private string  $checkName;
    private string  $severity;
    private string  $message;
    private ?string $file;
    private ?int    $line;
    private string  $ruleCode;
    private array   $metadata;
    private string  $deleteClassification;

    public function __construct(
        string  $checkName,
        string  $severity,
        string  $message,
        ?string $file = null,
        ?int    $line = null,
        string  $ruleCode = '',
        ?string $deleteClassification = null
    )
    {
        $this->checkName            = $checkName;
        $this->severity             = $severity;
        $this->message              = $message;
        $this->file                 = $file;
        $this->line                 = $line;
        $this->ruleCode             = $ruleCode;
        $this->metadata             = [];
        $this->deleteClassification = $deleteClassification ?? self::DELETE_UNSAFE;
    }

    public static function fromArray(array $data) : self
    {
        return new self(
            $data['check_name'] ?? 'Unknown',
            $data['severity'] ?? self::SEVERITY_INFO,
            $data['message'] ?? '',
            $data['file'] ?? null,
            $data['line'] ?? null,
            $data['rule_code'] ?? '',
            $data['delete_classification'] ?? null
        );
    }

    public function getCheckName() : string
    {
        return $this->checkName;
    }

    public function getSeverity() : string
    {
        return $this->severity;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getFile() : ?string
    {
        return $this->file;
    }

    public function getLine() : ?int
    {
        return $this->line;
    }

    public function getRuleCode() : string
    {
        return $this->ruleCode;
    }

    public function getDeleteClassification() : string
    {
        return $this->deleteClassification;
    }

    public function isWarning() : bool
    {
        return $this->severity === self::SEVERITY_WARNING;
    }

    public function isInfo() : bool
    {
        return $this->severity === self::SEVERITY_INFO;
    }

    public function withMetadata(array $metadata) : self
    {
        $new           = clone $this;
        $new->metadata = array_merge($this->metadata, $metadata);

        return $new;
    }

    public function withDeleteClassification(string $classification) : self
    {
        $new                       = clone $this;
        $new->deleteClassification = $classification;

        return $new;
    }

    public function toArray() : array
    {
        return [
            'check_name'            => $this->checkName,
            'severity'              => $this->severity,
            'message'               => $this->message,
            'file'                  => $this->file,
            'line'                  => $this->line,
            'rule_code'             => $this->ruleCode,
            'location'              => $this->getLocation(),
            'delete_classification' => $this->deleteClassification,
            'can_auto_fix'          => $this->canAutoFix(),
            'can_delete'            => $this->canDelete(),
            'is_blocking'           => $this->isBlocking(),
            'metadata'              => $this->metadata,
        ];
    }

    public function getLocation() : string
    {
        $location = $this->file ?? 'unknown';
        if ($this->line !== null) {
            $location .= ':' . $this->line;
        }

        return $location;
    }

    public function canAutoFix() : bool
    {
        return $this->metadata['auto_fixable'] ?? false;
    }

    public function canDelete() : bool
    {
        return in_array($this->deleteClassification, [
            self::DELETE_SAFE,
            self::DELETE_PROBABLY_SAFE,
        ],              true);
    }

    public function isBlocking() : bool
    {
        return in_array($this->severity, [self::SEVERITY_CRITICAL, self::SEVERITY_ERROR], true);
    }

    public function toTodoLine() : string
    {
        $action = match (true) {
            $this->canAutoFix()     => '[AUTO-FIX]',
            $this->canDelete()      => '[DELETE-CANDIDATE]',
            $this->requiresReview() => '[REVIEW]',
            default                 => '[INFO]',
        };

        return sprintf(
            "- [ ] %s [%s] %s - %s",
            $action,
            strtoupper($this->severity),
            $this->checkName,
            $this->message . ' (' . $this->getLocation() . ')'
        );
    }

    public function requiresReview() : bool
    {
        return ! $this->canAutoFix() && ! $this->canDelete();
    }
}
