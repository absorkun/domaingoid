<?php

namespace App\Filament\Pages;

use \Filament\Auth\Pages\Login as FilamentLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

class Login extends FilamentLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getPasswordFormComponent(),
                // $this->getCaptchaFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }
    
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Username')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getCaptchaFormComponent(): Component
    {
        return CaptchaField::make('captcha')
            ->validationMessages([
                'required' => 'Captcha perlu diisi'
            ]);
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'name' => $data['name'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
