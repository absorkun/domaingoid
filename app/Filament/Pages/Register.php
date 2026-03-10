<?php

namespace App\Filament\Pages;

use \Filament\Auth\Pages\Register as FilamentRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

class Register extends FilamentRegister
{
    // protected string $view = 'filament.pages.register';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFullNameFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormCommponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCaptchaFormComponent(),
            ]);
    }

    protected function getFullNameFormComponent(): Component
    {
        return TextInput::make('full_name')
            ->label('Nama Lengkap')
            ->placeholder('John Doe')
            ->required()
            ->maxLength(100)
            ->autofocus();
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Username')
            ->required()
            ->maxLength(50)
            ->autofocus();
    }

    protected function getPhoneFormCommponent(): Component
    {
        return TextInput::make('phone')
            ->label('No Whatsapp')
            ->placeholder('08123456789 atau +628123456789')
            ->tel()
            ->required();
    }

    protected function getCaptchaFormComponent(): Component
    {
        return CaptchaField::make('captcha')
            ->validationMessages([
                'required' => 'Captcha belum dicentang'
            ]);
    }
}
