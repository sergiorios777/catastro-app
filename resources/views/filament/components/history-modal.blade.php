<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wider text-gray-500 dark:bg-white/5 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Versión</th>
                    <th class="px-4 py-3">Vigencia Fiscal</th>
                    <th class="px-4 py-3 text-right">Área</th>
                    <th class="px-4 py-3">Detalles Técnicos</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @foreach($records as $record)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5 {{ $record->is_active ? 'bg-primary-50/40 dark:bg-primary-500/10' : '' }}">
                        
                        {{-- Columna: Versión --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 ring-1 ring-inset ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-500/30">
                                    v{{ $record->version }}
                                </span>
                                @if($record->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                        Actual
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Columna: Vigencia --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $record->valid_from?->format('d M Y') }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    hasta {{ $record->valid_to ? $record->valid_to->format('d M Y') : 'la actualidad' }}
                                </span>
                            </div>
                        </td>

                        {{-- Columna: Área --}}
                        <td class="px-4 py-3 text-right font-mono font-medium text-gray-900 dark:text-white">
                            {{ number_format($record->area_construida, 2) }} m²
                        </td>

                        {{-- Columna: Detalles (Combinamos columnas para ahorrar espacio) --}}
                        <td class="px-4 py-3">
                            <div class="grid gap-1">
                                <div class="flex items-center gap-1.5 text-xs">
                                    <x-heroicon-m-cube class="h-3 w-3 text-gray-400"/>
                                    <span>{{ ucfirst($record->material_estructural) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs">
                                    <x-heroicon-m-home-modern class="h-3 w-3 text-gray-400"/>
                                    <span>{{ ucfirst(str_replace('_', ' ', $record->uso_especifico)) }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Columna: Estado Conservación --}}
                        <td class="px-4 py-3 text-center">
                            @php
                                $color = match($record->estado_conservacion) {
                                    'muy_bueno' => 'text-green-600 bg-green-50 ring-green-600/20 dark:bg-green-500/10 dark:text-green-400',
                                    'bueno' => 'text-blue-600 bg-blue-50 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400',
                                    'regular' => 'text-orange-600 bg-orange-50 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400',
                                    'malo' => 'text-red-600 bg-red-50 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400',
                                    default => 'text-gray-600 bg-gray-50 ring-gray-600/20 dark:bg-gray-500/10 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $record->estado_conservacion)) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($records->isEmpty())
        <div class="p-4 text-center text-sm text-gray-500">
            No hay historial disponible para este registro.
        </div>
    @endif
</div>