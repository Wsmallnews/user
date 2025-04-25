<div class="w-full">
    <div class="w-full flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <div class="text-base font-bold">
                确认收货地址
            </div>
    
            <div class="flex justify-between items-center gap-4">
                {{$this->createAction}}
                {{$this->manageAction}}
            </div>
        </div>
    
        <x-filament::grid
            :default="$this->getColumns('default')"
            :sm="$this->getColumns('sm')"
            :md="$this->getColumns('md')"
            :lg="$this->getColumns('lg')"
            :xl="$this->getColumns('xl')"
            :two-xl="$this->getColumns('2xl')"
            class="gap-4"
        >
            @foreach($addresses as $key => $address)
                <div
                    @class([
                        'w-full flex flex-col rounded-lg p-4',
                        "ring-1 ring-gray-950/10 hover:ring-2 dark:ring-white/20 hover:ring-primary-600" => !$current || $current->id != $address->id,
                        'ring-2 ring-primary-600' => $current && $current->id == $address->id,
                    ])
                    @if(!$current || $current->id != $address->id)
                    wire:click="choose({{$address->id}})"
                    @endif
                >
                    <div class="flex">
                        @if($address->is_default)
                            <x-filament::badge size="sm">
                                默认
                            </x-filament::badge>
                        @endif
                        <div class="text-sm text-gray-400">
                            {{ $address->province_name }} {{ $address->city_name }} {{ $address->district_name }}
                        </div>
                    </div>
                    <div class="text-base my-1">{{ $address->address }} {{ $address->street_number }}</div>
                    <div class="flex text-base">
                        <div class="mr-2.5">{{ $address->consignee }}</div>
                        <div>{{ $address->mobile }}</div>
                    </div>
    
                    <div class="flex justify-end">
                        <div>
                            {{ ($this->editAction)(['id' => $address->id]) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </x-filament::grid>
    </div>

    <x-filament-actions::modals />
</div>