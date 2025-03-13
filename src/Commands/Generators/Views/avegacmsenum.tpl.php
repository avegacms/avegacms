<@php
declare(strict_types=1);

namespace {namespace};

enum {class}: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(self::cases(), $key) :
            self::cases();
    }

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum->value;
            }
        }

        return '';
    }

    public static function list(): array
    {
        $list = [];

        foreach (self::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
    }
    case Name  = 'VALUE';
}
