<?php

namespace Wsmallnews\User\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Collection;
use Livewire\Component;
use Wsmallnews\Support\Concerns\HasColumns;
use Wsmallnews\Support\Filament\Forms\Fields\DistrictSelect;
use Wsmallnews\User\Contracts\UserInterface;
use Wsmallnews\User\Enums;
use Wsmallnews\User\Models\Address as UserAddressModel;

class ChooseAddress extends Component implements HasActions, HasForms
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

    public Collection $addresses;

    public $current = null;

    public function mount($columns = null)
    {
        $this->columns($columns);

        $this->addresses = $this->user->addresses()->orderBy('is_default', 'desc')->orderBy('id', 'desc')->get();
    }

    public function createAction(): Action
    {
        return CreateAction::make()
            ->label('使用新地址')
            ->mutateFormDataUsing(function (array $data): array {
                // 处理数据
                return $this->operDistrict($data);
            })
            ->form($this->schema())
            ->using(function (array $data): UserAddressModel {
                return $this->user->addresses()->create($data);
            })
            ->link()
            ->createAnother(false)
            ->successNotificationTitle('创建成功');
    }

    public function editAction(): Action
    {
        return EditAction::make('edit')
            ->record(function (array $arguments) {
                $id = $arguments['id'] ?? 0;

                return $this->user->addresses()->findOrFail($id);
            })
            ->mutateFormDataUsing(function (array $data): array {
                // 处理数据
                return $this->operDistrict($data);
            })
            ->form($this->schema())
            ->link()
            ->successNotificationTitle('编辑成功');
    }

    public function manageAction(): Action
    {
        return Action::make('manage')
            ->label('管理地址')
            ->link()
            ->url(fn (): string => '跳转新地址');
    }

    public function choose($id)
    {
        $this->current = $this->addresses->where('id', $id)->first();
    }

    private function schema()
    {
        return [
            TextInput::make('consignee')
                ->label('收货人')
                ->placeholder('请输入收货人')
                ->required(),
            Radio::make('gender')
                ->label('性别')
                ->default(Enums\Gender::Other)
                ->inline()
                ->options(Enums\Gender::class),
            TextInput::make('mobile')
                ->label('手机号')
                ->required()
                ->rules(['regex:/^1[3456789][0-9]{9}$/']),
            DistrictSelect::make('district')
                ->label('地区')
                ->required(),
            Textarea::make('address')
                ->label('详细地址')
                ->required(),
        ];
    }

    /**
     * 这里处理省市区数据
     */
    private function operDistrict(array $data): array
    {
        $district = $data['district'];
        $data['province_name'] = $district['province_name'] ?? null;
        $data['province_id'] = $district['province_id'] ?? 0;
        $data['city_name'] = $district['city_name'] ?? null;
        $data['city_id'] = $district['city_id'] ?? 0;
        $data['district_name'] = $district['district_name'] ?? null;
        $data['district_id'] = $district['district_id'] ?? 0;
        unset($data['district']);

        return $data;
    }

    public function render()
    {
        return view('sn-user::livewire.choose-address.index', [
        ])->title('选择收货地址');
    }
}
