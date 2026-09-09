<?php

namespace App\Service;

class MaintenanceModeService
{
    public function __construct(private readonly string $projectDir)
    {
    }

    private function flagPath(): string
    {
        return $this->projectDir.'/var/maintenance.flag';
    }

    public function isEnabled(): bool
    {
        return is_file($this->flagPath());
    }

    public function enable(): void
    {
        file_put_contents($this->flagPath(), (new \DateTimeImmutable())->format('c'));
    }

    public function disable(): void
    {
        if ($this->isEnabled()) {
            unlink($this->flagPath());
        }
    }
}
