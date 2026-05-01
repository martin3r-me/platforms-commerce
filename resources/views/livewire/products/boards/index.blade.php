{{--
    Products Boards Index View
    Übersicht aller Produkt-Boards
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Produkt-Boards" icon="heroicon-o-folder" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Produkt-Boards</h1>
                    <p class="text-[13px] text-gray-500 mt-1">Verwalte deine Produkt-Boards</p>
                </div>
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

            {{-- Boards Liste --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Boards</h3>
                    <p class="text-[11px] text-gray-500">{{ count($productBoards) }} Board(s)</p>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($productBoards as $board)
                        <a href="{{ route('commerce.products.boards.show', $board) }}" class="block p-4 rounded-md border border-gray-200 bg-white hover:bg-blue-50/50 transition-colors" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-[13px] font-semibold text-gray-900">{{ $board->name }}</h3>
                                    @if($board->description)
                                        <p class="text-[13px] text-gray-500 mt-1">{{ Str::limit($board->description, 100) }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-4 text-[11px] text-gray-400">
                                        <span>{{ $board->productBoardSlots()->count() }} Slot(s)</span>
                                        <span>{{ $board->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-gray-500 bg-white rounded-md border border-gray-200">
                            <p class="text-[13px]">Noch keine Boards vorhanden.</p>
                        </div>
                    @endforelse
                </div>
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
