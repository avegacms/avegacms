<@php
declare(strict_types=1);

namespace {namespace};

enum {class}: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column({class}::cases(), $key) :
            {class}::cases();
    }

    public static function fromName(string $name): string
    {
        foreach ({class}::cases() as $enum) {
            if ($enum->name === $name) {
                return $enum->value;
            }
        }

        return '';
    }

    public static function list(): array
    {
        $list = [];

        foreach ({class}::cases() as $enum) {
            $list[] = ['label' => $enum->value, 'value' => $enum->name];
        }

        return $list;
    }
    case Name  = 'VALUE';
}
