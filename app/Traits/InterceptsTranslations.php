<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait InterceptsTranslations
{
    public function getAttribute($key)
    {
        $locale = App::getLocale();
        $translationKey = $key . '_translation';

        $attributes = $this->getAttributes();

        if ($locale !== 'en' && array_key_exists($translationKey, $attributes)) {
            $translations = parent::getAttribute($translationKey);

            if (is_array($translations)) {
                return $translations[$locale]
                    ?? $translations['en']
                    ?? parent::getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    public function attributesToArray()
    {
        $locale = App::getLocale();

        // Raw attributes lo, hidden se pehle
        $attributes = $this->getAttributes();

        // Casted translation arrays bhi proper mil jayein
        foreach ($attributes as $key => $value) {
            if (str_ends_with($key, '_translation')) {
                $attributes[$key] = $this->castAttribute($key, $value);
            }
        }

        if ($locale !== 'en') {
            foreach ($attributes as $key => $value) {
                if (str_ends_with($key, '_translation')) {
                    continue;
                }

                $translationKey = $key . '_translation';

                if (
                    array_key_exists($translationKey, $attributes) &&
                    is_array($attributes[$translationKey])
                ) {
                    $attributes[$key] = $attributes[$translationKey][$locale]
                        ?? $attributes[$translationKey]['en']
                        ?? $value;
                }
            }
        }

        // response se translation fields hata do
        unset(
            $attributes['tag_translation'],
            $attributes['title_translation'],
            $attributes['duration_translation']
        );

        return $attributes;
    }
}