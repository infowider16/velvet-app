<?php

return [
    'digits' => ':attribute muss genau :digits Ziffern haben.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'email' => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'password_incorrect' => 'Das alte Passwort ist falsch.',
    'same' => ':attribute und :other müssen übereinstimmen.',
    'confirmed' => 'Die Passwortbestätigung stimmt nicht überein.',
    'string' => ':attribute muss eine Zeichenkette sein.',
    'array' => ':attribute muss ein Array sein.',
    'date' => ':attribute ist kein gültiges Datum.',
    'in' => 'Der ausgewählte Wert für :attribute ist ungültig.',
    'before_or_equal' => ':attribute muss ein Datum vor oder gleich :date sein.',
    'image' => ':attribute muss ein Bild sein.',
    'mimes' => ':attribute muss eine Datei vom Typ :values sein.',
    'boolean' => 'Das Feld :attribute muss wahr oder falsch sein.',
    'unique' => ':attribute ist bereits vergeben.',
    'max' => [
        'string' => ':attribute darf nicht mehr als :max Zeichen enthalten.',
        'file' => ':attribute darf nicht größer als :max Kilobyte sein.',
    ],
    'numeric' => ':attribute muss eine Zahl sein.',
    'integer' => ':attribute muss eine ganze Zahl sein.',
    'exists' => ':attribute muss eine gültige ID sein.',
    'between' => [
        'numeric' => ':attribute muss zwischen :min und :max liegen.',
    ],

    'first_name' => 'Vorname',
    'last_name' => 'Nachname',
    'refferal_code' => 'Der eingegebene Empfehlungscode ist ungültig. Bitte überprüfen Sie ihn und versuchen Sie es erneut.',
    'required_without' => 'Das Feld :attribute ist erforderlich.',
    'required_with' => 'Das Feld :attribute ist zusammen mit :other erforderlich.',

    'custom' => [
        'store_owner_name' => [
            'required' => 'Das Feld Geschäftsinhaber ist erforderlich.',
        ],
        'store_name' => [
            'required' => 'Das Feld Geschäftsname ist erforderlich.',
        ],
        'registration_no' => [
            'required' => 'Das Feld Registrierungsnummer ist erforderlich.',
            'unique' => 'Die Registrierungsnummer ist bereits vergeben.',
        ],
        'document_proof' => [
            'required' => 'Der Dokumentennachweis ist erforderlich.',
        ],
        'phone_no' => [
            'required' => 'Das Feld Telefonnummer ist erforderlich.',
            'unique' => 'Die Telefonnummer ist bereits vergeben.',
        ],
        'email' => [
            'required' => 'Das Feld E-Mail ist erforderlich.',
            'unique' => 'Die E-Mail ist bereits vergeben.',
        ],
        'password' => [
            'required' => 'Das Feld Passwort ist erforderlich.',
            'min' => 'Das Passwort muss mindestens :min Zeichen lang sein.',
        ],
        'address' => [
            'required' => 'Das Feld Adresse ist erforderlich.',
        ],
        'old_password' => [
            'required' => 'Das Feld Altes Passwort ist erforderlich.',
        ],
        'new_password' => [
            'required' => 'Das Feld Neues Passwort ist erforderlich.',
            'min' => 'Das neue Passwort muss mindestens :min Zeichen lang sein.',
        ],
        'full_name' => [
            'required' => 'Das Feld Vollständiger Name ist erforderlich.',
        ],
        'message' => [
            'required' => 'Das Feld Nachricht ist erforderlich.',
        ],
        'amount' => [
            'required' => 'Das Feld Betrag ist erforderlich.',
            'numeric' => 'Der Betrag muss eine Zahl sein.',
        ],
        'receiver_number' => [
            'required' => 'Das Feld Empfängernummer ist erforderlich.',
        ],
        'confirm_password' => [
            'required' => 'Die Passwortbestätigung ist erforderlich.',
            'same' => 'Die Passwortbestätigung muss mit dem neuen Passwort übereinstimmen.',
            'required_with' => 'Die Passwortbestätigung ist erforderlich, wenn ein neues Passwort vorhanden ist.',
        ],
        'user_id' => [
            'invalid_user_id' => 'Die Benutzer-ID :id existiert nicht.',
            'required' => 'Die Benutzer-ID ist erforderlich.',
        ],
        'group_id' => [
            'required' => 'Die Gruppen-ID ist erforderlich.',
            'integer' => 'Die Gruppen-ID muss eine ganze Zahl sein.',
            'exists' => 'Die Gruppe existiert nicht.',
        ],
        'role' => [
            'in' => 'Die Rolle muss entweder member oder admin sein.',
        ],
       'phone_number' => [
            'required' => 'Die Telefonnummer ist erforderlich.',
            'string'   => 'Die Telefonnummer muss eine Zeichenkette sein.',
            'unique_combination' => 'Die Kombination aus Ländervorwahl und Telefonnummer ist bereits registriert.',
        ],
        'phone_code' => [
            'required' => 'Das Feld Ländervorwahl ist erforderlich.',
            'string' => 'Die Ländervorwahl muss eine Zeichenkette sein.',
        ],
        'country_code' => [
            'string' => 'Der Ländercode muss eine Zeichenkette sein.',
        ],
        'name' => [
            'required' => 'Der Name ist erforderlich.',
            'string' => 'Der Name muss eine Zeichenkette sein.',
            'max' => 'Der Name darf nicht länger als :max Zeichen sein.',
            'unique' => 'Der Name ist bereits vergeben.',
        ],
        'date_of_birth' => [
            'date' => 'Das Geburtsdatum muss ein gültiges Datum sein.',
            'before_or_equal' => 'Sie müssen mindestens 18 Jahre alt sein.',
        ],
        'gender' => [
            'in' => 'Das Geschlecht muss männlich, weiblich oder andere sein.',
        ],
        'interest_id' => [
            'array' => 'Interessen müssen als Array übermittelt werden.',
        ],
        'interest_id.*' => [
            'integer' => 'Jede Interessen-ID muss eine ganze Zahl sein.',
            'exists' => 'Die ausgewählte Interessen-ID ist ungültig.',
        ],
        'about_me' => [
            'max' => 'Über mich darf nicht länger als 1000 Zeichen sein.',
        ],
        'latitude' => [
            'between' => 'Der Breitengrad muss zwischen -90 und 90 liegen.',
        ],
        'longitude' => [
            'between' => 'Der Längengrad muss zwischen -180 und 180 liegen.',
        ],
        'location' => [
            'string' => 'Standort muss eine Zeichenkette sein.',
            'max' => 'Standort darf nicht länger als 255 Zeichen sein.',
        ],
        'subject' => [
            'string' => 'Betreff muss eine Zeichenkette sein.',
            'max' => 'Betreff darf nicht länger als 255 Zeichen sein.',
        ],
        'image' => [
            'image' => 'Die Datei muss ein Bild sein.',
            'mimes' => 'Das Bild muss eine Datei vom Typ jpeg, png, jpg, gif, svg sein.',
            'max' => 'Das Bild darf nicht größer als 2MB sein.',
        ],
        'gmail_id' => [
            'required' => 'E-Mail ist erforderlich.',
            'email' => 'E-Mail muss eine gültige E-Mail-Adresse sein.',
            'max' => 'E-Mail darf nicht länger als :max Zeichen sein.',
        ],
        'google_id' => [
            'required' => 'Google-ID ist erforderlich.',
            'string' => 'Google-ID muss eine Zeichenkette sein.',
            'max' => 'Google-ID darf nicht länger als :max Zeichen sein.',
        ],
        'lat' => [
            'numeric' => 'Der Breitengrad muss eine Zahl sein.',
        ],
        'lng' => [
            'numeric' => 'Der Längengrad muss eine Zahl sein.',
        ],
        'images' => [
            'required' => 'Mindestens ein Bild ist erforderlich.',
            'array' => 'Bilder müssen als Array gesendet werden.',
            'min' => 'Sie müssen mindestens ein Bild hochladen.',
        ],

        'images.*' => [
            'required' => 'Jedes Bild ist erforderlich.',
            'image' => 'Jede Datei muss ein gültiges Bild sein.',
            'mimes' => 'Jedes Bild muss vom Typ jpeg, png, jpg, gif oder svg sein.',
            'max' => 'Jedes Bild darf nicht größer als 2MB sein.',
        ],
        'phone_number' => [
            'required' => 'Die Telefonnummer ist erforderlich.',
            'string' => 'Die Telefonnummer muss eine Zeichenkette sein.',
            'exists' => 'Die angegebene Telefonnummer ist nicht registriert.',
        ],

        'phone_code' => [
            'required' => 'Die Ländervorwahl ist erforderlich.',
            'string' => 'Die Ländervorwahl muss eine Zeichenkette sein.',
            'exists' => 'Die angegebene Ländervorwahl ist ungültig.',
        ],

        'country_code' => [
            'required' => 'Der Ländercode ist erforderlich.',
            'string' => 'Der Ländercode muss eine Zeichenkette sein.',
            'exists' => 'Der angegebene Ländercode ist ungültig.',
        ],

        'otp' => [
            'required' => 'OTP ist erforderlich.',
            'digits' => 'OTP muss genau 6 Ziffern haben.',
            'invalid' => 'Ungültiges OTP.',
            'expired' => 'OTP ist abgelaufen.',
            'user_not_found' => 'Benutzer wurde nicht gefunden.',
        ],
        'location_consent' => [
            'required' => 'Die Standortfreigabe ist erforderlich.',
            'boolean' => 'Die Standortfreigabe muss wahr oder falsch sein.',
        ],
        
    ],

    'attributes' => [
        'store_owner_name' => 'Geschäftsinhaber',
        'store_name' => 'Geschäftsname',
        'registration_no' => 'Registrierungsnummer',
        'document_proof' => 'Dokumentennachweis',
        'phone_no' => 'Telefonnummer',
        'email' => 'E-Mail-Adresse',
        'password' => 'Passwort',
        'address' => 'Adresse',
        'old_password' => 'Altes Passwort',
        'new_password' => 'Neues Passwort',
        'full_name' => 'Vollständiger Name',
        'message' => 'Nachricht',
        'amount' => 'Betrag',
        'receiver_number' => 'Empfängernummer',
        'location' => 'Standort',
        'latitude' => 'Breitengrad',
        'longitude' => 'Längengrad',
        'group_id' => 'Gruppen-ID',
        'user_id' => 'Benutzer-ID',
        'role' => 'Rolle',
        'name' => 'Name',
        'date_of_birth' => 'Geburtsdatum',
        'gender' => 'Geschlecht',
        'interest_id' => 'Interesse',
        'about_me' => 'Über mich',
        'images' => 'Bilder',
        'subject' => 'Betreff',
        'image' => 'Bild',
        'phone_number' => 'Telefonnummer',
        'phone_code' => 'Ländervorwahl',
        'country_code' => 'Ländercode',
        'gmail_id' => 'E-Mail',
        'google_id' => 'Google-ID',
        'lat' => 'Breitengrad',
        'lng' => 'Längengrad',
        'push_notification_status' => 'Push-Benachrichtigungsstatus',
        'images' => 'Bilder',
        'images.*' => 'Bild',
        'otp' => 'OTP',
        'location_consent' => 'Standortfreigabe',
    ],
];
