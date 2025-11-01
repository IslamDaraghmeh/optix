@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'deleteConfirm' => 'Are you sure you want to delete this item?',
    'customActions' => []
])

<div class="flex action-buttons justify-end space-x-2">
    @if($viewUrl)
        <a href="{{ $viewUrl }}"
           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 hover:bg-primary-200 transition-colors duration-200">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                </path>
            </svg>
            {{ __('app.view') }}
        </a>
    @endif

    @if($editUrl)
        <a href="{{ $editUrl }}"
           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                </path>
            </svg>
            {{ __('app.edit') }}
        </a>
    @endif

    @foreach($customActions as $action)
        @if($action['type'] === 'link')
            <a href="{{ $action['url'] }}"
               class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $action['class'] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} transition-colors duration-200">
                @if(isset($action['icon']))
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $action['icon'] !!}
                    </svg>
                @endif
                {{ $action['label'] }}
            </a>
        @elseif($action['type'] === 'button')
            <button type="button"
                    onclick="{{ $action['onclick'] }}"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $action['class'] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} transition-colors duration-200">
                @if(isset($action['icon']))
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $action['icon'] !!}
                    </svg>
                @endif
                {{ $action['label'] }}
            </button>
        @endif
    @endforeach

    @if($deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" class="inline"
              onsubmit="return confirm('{{ $deleteConfirm }}')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition-colors duration-200">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
                {{ __('app.delete') }}
            </button>
        </form>
    @endif
</div>

<style>
    /* RTL Support for Action Buttons */
    [dir="rtl"] .action-buttons {
        flex-direction: row-reverse;
    }

    [dir="rtl"] .action-buttons .space-x-2 > * + * {
        margin-left: 0;
        margin-right: 0.5rem;
    }

    [dir="ltr"] .action-buttons {
        flex-direction: row;
    }

    /* Icon spacing for RTL/LTR */
    [dir="rtl"] .action-buttons svg {
        margin-right: 0;
        margin-left: 0.25rem;
    }

    [dir="ltr"] .action-buttons svg {
        margin-right: 0.25rem;
        margin-left: 0;
    }
</style>
