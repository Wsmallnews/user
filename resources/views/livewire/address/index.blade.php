<div class="w-full" >
    <div class="flex justify-end">
        {{$this->createAction}}
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
                    "border-gray-200" => !$current || $current->id != $address->id,
                    'border-primary-500' => $current && $current->id == $address->id,
                ])
                @if($type == 'choose' && (!$current || $current->id != $address->id))
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
                    @if($type == 'manager')
                        <div class="mr-2.5">
                            {{ ($this->setDefaultAction)(['id' => $address->id]) }}
                        </div>
                    @endif
                    <div>
                        {{ ($this->editAction)(['id' => $address->id]) }}
                    </div>
                    @if($type == 'manager')
                        <div class="ml-2.5">
                            {{ ($this->deleteAction)(['id' => $address->id]) }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </x-filament::grid>



    {{-- <div class="grid grid-cols-1 md:grid-cols-3 2xl:grid-cols-4 gap-4 w-full my-4">
        @foreach($addresses as $key => $address)

        @endforeach
    </div> --}}

    <x-filament-actions::modals />
</div>


@assets
<script>

</script>
@endassets