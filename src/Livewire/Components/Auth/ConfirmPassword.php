<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class ConfirmPassword extends Base implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('password')
                    ->label('密码')
                    ->aboveLabel('敏感操作，请在继续之前确认您的密码。')
                    ->placeholder('请确认密码')
                    ->required()
                    ->password()
                    ->revealable(),
            ])
            ->statePath('formData');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('confirmPassword')
                ->label('确认密码')
                ->submit('confirmPassword'),
        ];
    }

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $formData = $this->form->getState();

        // 当前 guard
        $guard = UserConfig::getConfig($this->module, 'guard');

        if (! Auth::guard($guard)->validate([
            'email' => Auth::guard($guard)->user()->email,
            'password' => $formData['password'],
        ])) {
            $this->addError('formData.password', __('auth.password'));

            return;
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: UserConfig::getConfig($this->module, 'urls.index'), navigate: FilamentView::hasSpaMode());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): SchemaComponent
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('confirmPassword')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('confirm-password-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.confirm-password', []);
    }
}
