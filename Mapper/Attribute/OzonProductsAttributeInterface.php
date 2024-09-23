<?php

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Mapper\Attribute;


use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ozon.product.attribute')]
interface OzonProductsAttributeInterface

{

    public function getId(): int;


    public function getData(array $data): mixed;

    /** Возвращает значение по умолчанию */
    public function default(): string|false;

    /** Добавить в настройку */
    public function isSetting(): bool;

    /** Обязателен к заполнению */
    public function required(): bool;

    /** Массив допустимых значений */
    public function choices(): array|false;

    /**
     * Сортировка (чем выше число - тем первым в итерации будет значение)
     */
    public static function priority(): int;

    /** Проверяет, относится ли значение к данному объекту */
    public static function equals(int|string $param): bool;

    /** Проверяет, относится ли объект к указанной категории */
    public function equalsCategory(int $category): bool;

}



