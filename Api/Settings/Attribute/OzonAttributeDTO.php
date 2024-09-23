<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Ozon\Products\Api\Settings\Attribute;

final readonly class OzonAttributeDTO
{
    /** Номер задания на формирование документов */
    private int $id;

    /** Идентификатор комплексного атрибута */
    private int $complex;

    /** Наименование */
    private string $name;

    /** Описание характеристики */
    private string $description;

    /** Тип характеристики */
    private string $type;

    /**
     * true, если характеристика — набор значений,
     * false, если характеристика — одно значение.
     */
    private bool $collection;

    /** Признак обязательной характеристики */
    private bool $required;

    /** Максимальное количество значений для атрибута */
    private int $count;

    /** Идентификатор группы характеристик. */
    private int $groupId;

    /** Название группы характеристик */
    private string $groupName;

    /** Идентификатор справочника */
    private int $dictionary;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->complex      = $data['attribute_complex_id'];
        $this->name         = $data['name'];
        $this->description  = $data['description'];
        $this->type         = $data['type'];
        $this->collection   = $data['is_collection'];
        $this->required     = $data['is_required'];
        $this->count        = $data['max_value_count'];
        $this->groupId      = $data['group_id'];
        $this->groupName    = $data['group_name'];
        $this->dictionary   = $data['dictionary_id'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getComplexId(): int
    {
        return $this->complex;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getMaxValueCount(): int
    {
        return $this->count;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getDictionaryId(): int
    {
        return $this->dictionary;
    }

}
