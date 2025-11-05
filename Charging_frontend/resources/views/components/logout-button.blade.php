<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
            class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">
        登出
    </button>
</form>
