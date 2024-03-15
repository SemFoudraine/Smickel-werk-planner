<nav x-data="{ open: false }" class="bg-blue-900 border-b border-black">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex">
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white dark:white">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('roosters.index')" :active="request()->routeIs('roosters')" class="text-white dark:white">
                        {{ __('Rooster') }}
                    </x-nav-link>
                    <x-nav-link :href="route('verlofaanvragen.index')" :active="request()->routeIs('verlofaanvragen')" class="text-white dark:white">
                        {{ __('Verlof Aanvragen') }}
                    </x-nav-link>
                    <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications')" class="text-white dark:white">
                        {{ __('Meldingen') }}
                        @if ($unreadNotificationsCount > 0)
                            <span class="unread-indicator">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </x-nav-link>
                    @if (Auth::user()->hasRole('admin'))
                        <x-nav-link :href="route('admin.index')" :active="request()->routeIs('beheer')" class="text-white dark:white">
                            {{ __('Beheer') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-black dark:text-gray-300 bg-slate-100 dark:bg-gray-800 hover:text-black dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <!-- Dropdown Icon -->
                                <svg :class="{ 'hidden': open, 'block': !open }" class="block fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                <svg :class="{ 'block': open, 'hidden': !open }" class="hidden fill-current h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 18L18 6M6 6l12 12" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Dropdown content -->
                        <x-dropdown-link :href="route('users.edit', auth()->user()->id)" class="text-gray-700 dark:text-gray-300">
                            {{ __('Profiel') }}
                        </x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();"
                                class="text-gray-700 dark:text-gray-300">
                                {{ __('Uitloggen') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <!-- Hamburger Icon -->
                    <svg :class="{ 'hidden': open, 'block': !open }" class="block h-6 w-6" stroke="currentColor"
                        fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Close Icon -->
                    <svg :class="{ 'block': open, 'hidden': !open }" class="hidden h-6 w-6" stroke="currentColor"
                        fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <!-- Responsive Links -->
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-300">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('roosters.index')" :active="request()->routeIs('roosters')" class="text-gray-300">
                {{ __('Rooster') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('verlofaanvragen.index')" :active="request()->routeIs('verlofaanvragen')" class="text-gray-300">
                {{ __('Verlof Aanvragen') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications')" class="text-gray-300">
                {{ __('Meldingen') }}
                @if ($unreadNotificationsCount > 0)
                    <span class="unread-indicator">{{ $unreadNotificationsCount }}</span>
                @endif
            </x-responsive-nav-link>
            @if (Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('beheer')" class="text-gray-300">
                    {{ __('Beheer') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('users.edit', auth()->user()->id)" class="text-gray-700">
                    {{ __('Profiel') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();"
                        class="text-gray-700">
                        {{ __('Uitloggen') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<style>
    .unread-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 5px;
        min-width: 16px;
        /* Zorg voor voldoende breedte voor twee cijfers */
        height: 16px;
        border-radius: 50%;
        background-color: red;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
</style>
