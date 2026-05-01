{{--
    Products Boards Index View
    Übersicht aller Produkt-Boards
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Produkt-Boards" icon="heroicon-o-folder" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Boards'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Info-Box --}}
            <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                @svg('heroicon-o-information-circle', 'w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0')
                <p class="text-[13px] text-blue-800">Boards organisieren Produkte in einer Kanban-Ansicht. Erstelle Slots (Spalten) und ordne Produkte per Drag & Drop zu.</p>
            </div>

            {{-- Create Board Form --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Neues Board erstellen</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                            <input type="text"
                                   wire:model="name"
                                   placeholder="Board Name eingeben..."
                                   required
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            <button wire:click="createProductBoard"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Board erstellen
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Boards Tabelle --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Boards</h3>
                    <p class="text-[11px] text-gray-500">{{ count($productBoards) }} Board(s)</p>
                </div>
                @if(count($productBoards) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Name</th>
                                <th class="text-center text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Slots</th>
                                <th class="text-center text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Produkte</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Erstellt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productBoards as $board)
                                <tr class="border-b border-gray-100 hover:bg-blue-50/50 transition-colors cursor-pointer"
                                    wire:key="board-{{ $board->id }}"
                                    x-on:click="window.Livewire.navigate('{{ route('commerce.products.boards.show', $board) }}')">
                                    <td class="py-2.5 px-4">
                                        <div class="text-[13px] font-medium text-gray-900">{{ $board->name }}</div>
                                        @if($board->description)
                                            <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ Str::limit($board->description, 80) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-center text-[13px] text-gray-700">{{ $board->product_board_slots_count }}</td>
                                    <td class="py-2.5 px-4 text-center text-[13px] text-gray-700">{{ $board->products_count }}</td>
                                    <td class="py-2.5 px-4 text-right text-[11px] text-gray-400">{{ $board->created_at->format('d.m.Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 mb-4">
                            @svg('heroicon-o-view-columns', 'w-6 h-6 text-[#166EE1]')
                        </div>
                        <h3 class="text-[13px] font-medium text-gray-900 mb-1">Noch keine Boards vorhanden</h3>
                        <p class="text-[13px] text-gray-500">Erstelle dein erstes Board, um Produkte visuell zu organisieren.</p>
                    </div>
                @endif
            </section>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-home', 'w-4 h-4')
                            Dashboard
                        </a>
                        <a href="{{ route('commerce.articles.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                            Artikel
                        </a>
                        <a href="{{ route('commerce.products.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-cube', 'w-4 h-4')
                            Produkte
                        </a>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-[13px] text-gray-500">Letzte Aktivitäten</div>
                <div class="space-y-2">
                    <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                        <div class="font-medium text-gray-900 text-[13px] truncate">Boards-Übersicht geladen</div>
                        <div class="text-[11px] text-gray-500">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
