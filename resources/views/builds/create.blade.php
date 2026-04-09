@extends('layouts.app')
@section('title', 'New Build')

@section('content')
<div class="container section">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">Create New Build</h1>
            <p class="text-secondary">Start a new house construction project</p>
        </div>

        <div class="card">
            <form action="{{ route('builds.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <x-input
                    label="Build Name"
                    name="name"
                    placeholder="My Dream House"
                    :required="true"
                    autocomplete="off"
                />

                <x-textarea
                    label="Description"
                    name="description"
                    placeholder="Describe your build project..."
                    :rows="3"
                />

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn--secondary flex-1">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn--primary flex-1">
                        Create Build
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
