<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Values;

final readonly class FilePermissions
{
    public function __construct(
        public int $permissions,
    ) {}

    public function toOctal() : string
    {
        return sprintf('%04o', $this->permissions);
    }

    public function toSymbolic(bool $isDirectory = false) : string
    {
        $type  = $isDirectory ? 'd' : '-';
        $perms = '';
        $perms .= (($this->permissions & 0x0100) ? 'r' : '-');
        $perms .= (($this->permissions & 0x0080) ? 'w' : '-');
        $perms .= (($this->permissions & 0x0040) ? 'x' : '-');
        $perms .= (($this->permissions & 0x0020) ? 'r' : '-');
        $perms .= (($this->permissions & 0x0010) ? 'w' : '-');
        $perms .= (($this->permissions & 0x0008) ? 'x' : '-');
        $perms .= (($this->permissions & 0x0004) ? 'r' : '-');
        $perms .= (($this->permissions & 0x0002) ? 'w' : '-');
        $perms .= (($this->permissions & 0x0001) ? 'x' : '-');

        return $type . $perms;
    }
}