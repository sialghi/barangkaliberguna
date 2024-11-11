<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileNameValidator implements Rule
{
    protected $maxLength;

    public function __construct($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function passes($attribute, $value)
    {
        if (isset($value) && $value->getClientOriginalName()) {
            $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
            return strlen($filename) <= $this->maxLength;
        }
        return false;
    }

    public function message()
    {
        return "Nama :attribute harus kurang dari {$this->maxLength} karakter.";
    }
}