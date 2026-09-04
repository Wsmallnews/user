<div class="w-full">
    <div class="w-full flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <div class="sn-h3-text">
                {{ __('sn-user::user.address.my_addresses') }}
            </div>
    
            <div class="flex justify-between items-center gap-4">
                {{$this->createAction}}
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
                        'w-full flex flex-col border rounded-lg p-4',
                        "ring-1 ring-gray-950/10 hover:ring-2 dark:ring-white/20 hover:ring-primary-600",
                    ])
                >
                    <div class="flex">
                        @if($address->is_default)
                            <x-filament::badge size="sm">
                                {{ __('sn-user::user.address.default') }}
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
                        <div class="mr-2.5">
                            {{ ($this->setDefaultAction)(['id' => $address->id]) }}
                        </div>
                        <div>
                            {{ ($this->editAction)(['id' => $address->id]) }}
                        </div>
                        <div class="ml-2.5">
                            {{ ($this->deleteAction)(['id' => $address->id]) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </x-filament::grid>
    </div>

    <x-filament-actions::modals />
</div>