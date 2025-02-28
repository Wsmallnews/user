<?php

namespace Wsmallnews\User\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Wsmallnews\Support\Concerns\HasColumns;
use Wsmallnews\User\Contracts\UserInterface;
use Wsmallnews\User\Enums;
use Wsmallnews\User\Exceptions\UserException;
use Wsmallnews\User\Models\Address as UserAddressModel;

class Address extends Component implements HasActions, HasForms
{
    use HasColumns;
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * 用户操作
     *
     * @var UserInterface
     */
    public $user = null;

    public $type = 'manager';        // manager=管理;choose=选择

    public Collection $addresses;

    public $current = null;

    public function mount($columns = null)
    {
        $this->columns($columns);

        // @sn 这里用户信息需要重新处理
        $this->user = Auth::guard()->user();
        $this->user || throw new UserException('user 不存在');

        $this->addresses = $this->user->addresses()->orderBy('is_default', 'desc')->orderBy('id', 'desc')->get();
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->model(UserAddressModel::class)
            ->mutateFormDataUsing(function (array $data): array {
                // 这里处理省市区数据，用户 id 等
                // $data['user_id'] = auth()->id();
                return $data;
            })
            ->form($this->schema())
            ->createAnother(false)
            ->successNotificationTitle('创建成功');
    }

    public function editAction(): Action
    {
        return EditAction::make('edit')
            ->record(function (array $arguments) {
                $id = $arguments['id'] ?? 0;

                return UserAddressModel::where('user_id', $this->user->id)->findOrFail($id);
            })
            ->form($this->schema())
            ->link();
    }

    public function setDefaultAction(): Action
    {
        return Action::make('set_default')
            ->label('设为默认')
            ->record(function (array $arguments) {
                $id = $arguments['id'] ?? 0;

                return UserAddressModel::where('user_id', $this->user->id)->findOrFail($id);
            })
            ->action(function (UserAddressModel $record) {
                UserAddressModel::where('user_id', $this->user->id)->update(['is_default' => 0]);
                $record->is_default = 1;
                $record->save();
            })
            ->color('gray')
            ->requiresConfirmation()
            ->link();
    }

    public function deleteAction(): Action
    {
        return DeleteAction::make('delete')
            ->record(function (array $arguments) {
                $id = $arguments['id'] ?? 0;

                return UserAddressModel::where('user_id', $this->user->id)->findOrFail($id);
            })
            ->color('gray')
            ->requiresConfirmation()
            ->link();
    }

    public function choose($id)
    {
        $this->current = $this->addresses->where('id', $id)->first();
    }

    private function schema()
    {
        return [
            TextInput::make('consignee')
                ->required(),
            Radio::make('gender')
                ->default(1)
                ->inline()
                ->options(Enums\Gender::class),
            TextInput::make('mobile')
                ->required()
                ->rules(['regex:/^1[3456789][0-9]{9}$/']),
            Textarea::make('address')
                ->required(),
        ];
    }

    public function render()
    {
        return view('sn-user::livewire.address.index', [
        ])->title('我的收货地址');
    }
}
