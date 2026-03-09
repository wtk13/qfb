<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => __('business.title'), 'url' => route('business-profiles.index')],
            ['label' => __('business.create_title')],
        ]" />
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('business.create_title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('business-profiles.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="name" :value="__('business.name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="address" :value="__('business.address')" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="google_review_link" :value="__('business.google_review_link')" />
                        <x-text-input id="google_review_link" name="google_review_link" type="url" class="mt-1 block w-full" :value="old('google_review_link')" placeholder="https://g.page/r/..." />
                        <p class="text-xs text-gray-500 mt-1">{{ __('business.google_review_link_help') }}</p>
                        <x-input-error :messages="$errors->get('google_review_link')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="locale" :value="__('business.locale')" />
                        <select id="locale" name="locale" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="en" {{ old('locale') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="pl" {{ old('locale') === 'pl' ? 'selected' : '' }}>Polski</option>
                        </select>
                        <x-input-error :messages="$errors->get('locale')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="logo" :value="__('business.logo')" />
                        <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('business-profiles.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">{{ __('common.cancel') }}</a>
                        <x-primary-button>{{ __('common.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
