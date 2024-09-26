<?php

namespace BaksDev\Ozon\Products\Api\Card\Stocks\Info;

class OzonProductStockDTO
{
    public function __construct(
        /** Тип склада. */
        private string $type,

        /** Сейчас на складе. */
        private int $present,

        /** Зарезервировано */
        private int $reserved
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPresent(): int
    {
        return $this->present;
    }

    public function getReserved(): int
    {
        return $this->reserved;
    }

}
