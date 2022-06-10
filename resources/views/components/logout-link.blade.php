<form method="POST" action="{{ route('logout') }}">
    @csrf

    <a href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">
        <i class="fa-solid fa-door-closed"></i>
        {{ __('Log Out') }}
    </a>
</form>
