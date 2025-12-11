<?php declare(strict_types=1);

namespace Loki\Base\ViewModel\Block;

class ChildCounter
{
    private array $counters = [];

    public function getCounter(string $childName): int
    {
        if (isset($this->counters[$childName])) {
            $this->counters[$childName]++;
        } else {
            $this->counters[$childName] = 0;
        }

        return $this->counters[$childName];
    }
}
