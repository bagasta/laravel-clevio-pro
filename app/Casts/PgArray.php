<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PgArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) return null;

        // format pg: {a,b,"c,d"}
        $trimmed = trim($value, '{}');
        if ($trimmed === '') return [];

        // split coma tapi hormati kutipan
        $items = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $trimmed);

        return array_map(function ($v) {
            $v = trim($v);
            if (str_starts_with($v, '"') && str_ends_with($v, '"')) {
                $v = substr($v, 1, -1);
                $v = str_replace('\"', '"', $v);
            }
            return $v;
        }, $items);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) return null;

        $escaped = array_map(function ($v) {
            $v = str_replace('"', '\"', (string) $v);
            return '"'.$v.'"';
        }, $value);

        return '{'.implode(',', $escaped).'}';
    }
}
