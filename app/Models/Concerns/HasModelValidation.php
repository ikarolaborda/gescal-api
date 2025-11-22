<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasModelValidation
{
    public static function bootHasModelValidation(): void
    {
        static::creating(function (self $model) {
            $model->runModelValidation();
        });

        static::updating(function (self $model) {
            $model->runModelValidation();
        });
    }

    /**
     * Run model-level validation using the rules() method, if it exists.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function runModelValidation(): void
    {
        if (! method_exists($this, 'rules')) {
            return;
        }

        $validator = Validator::make(
            $this->attributesToArray(),
            $this->rules()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
