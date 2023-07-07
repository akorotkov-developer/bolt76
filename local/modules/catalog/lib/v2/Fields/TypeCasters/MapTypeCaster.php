<?php

namespace Bitrix\Catalog\v2\Fields\TypeCasters;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

// ToDo check it with real world on product/sku editing

/**
 * Class MapTypeCaster
 *
 * @package Bitrix\Catalog\v2\Fields\TypeCasters
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class MapTypeCaster implements TypeCasterContract
{
	// ToDo is it type caster or filter for fields also? if i need field with no type at all?
	public const NOTHING = 'nothing';

	public const STRING = 'string';
	public const NULLABLE_STRING = 'nullableString';

	public const INT = 'int';
	public const NULLABLE_INT = 'nullableInt';

	public const FLOAT = 'float';
	public const NULLABLE_FLOAT = 'nullableFloat';

	public const BOOLEAN = 'boolean';
	public const Y_OR_N = 'yesOrNo';
	public const Y_OR_N_OR_D = 'yesOrNoOrDefault';

	public const DATE = 'date';
	public const DATETIME = 'datetime';

	private $fieldMap = [];

	public function __construct(array $fieldMap = null)
	{
		if (!empty($fieldMap))
		{
			$this->fieldMap = $fieldMap;
		}
	}

	private function nothing($value)
	{
		return $value;
	}

	private function string($value): string
	{
		return (string)$value;
	}

	private function nullableString($value): ?string
	{
		if ($value !== null)
		{
			$value = (string)$value;
		}

		return $value;
	}

	private function int($value): int
	{
		return (int)$value;
	}

	private function nullableInt($value): ?int
	{
		if ($value !== null)
		{
			$value = (int)$value;
		}

		return $value;
	}

	private function float($value): float
	{
		return (float)$value;
	}

	private function nullableFloat($value): ?float
	{
		if ($value !== null)
		{
			$value = (float)$value;
		}

		return $value;
	}

	private function yesOrNo($value): string
	{
		if (is_bool($value))
		{
			return $value ? 'Y' : 'N';
		}

		return (string)$value === 'Y' ? 'Y' : 'N';
	}

	private function yesOrNoOrDefault($value): string
	{
		if (is_bool($value))
		{
			return $value ? 'Y' : 'N';
		}

		$value = (string)$value;

		if ($value !== 'Y' && $value !== 'D')
		{
			$value = 'N';
		}

		return $value;
	}

	private function boolean($value): bool
	{
		return (bool)$value;
	}

	private function date($value): Date
	{
		return new Date($value);
	}

	private function datetime($value): DateTime
	{
		return new DateTime($value);
	}

	public function cast($name, $value)
	{
		if ($value !== null && $this->has($name))
		{
			if (!is_callable([$this, $this->fieldMap[$name]]))
			{
				throw new NotSupportedException(sprintf(
					'Could not find casting {%s} for field {%s}.',
					$this->fieldMap[$name], $name
				));
			}

			return $this->{$this->fieldMap[$name]}($value);
		}

		return $value;
	}

	public function has($name): bool
	{
		return isset($this->fieldMap[$name]);
	}
}