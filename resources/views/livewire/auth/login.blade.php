
<div style="max-width:420px;margin:50px auto;background:white;padding:28px;border-radius:12px">

    <h1>Login Stock</h1>

    <form wire:submit="login">

        <label>Username</label>
        <input
            id="login-username"
            type="text"
            wire:model="username"
            required
            style="display:block;width:100%;padding:10px;margin:8px 0 16px"
        >

        @error('username')
            <p style="color:red">{{ $message }}</p>
        @enderror

        <label>Password</label>
        <input
            id="login-password"
            type="password"
            wire:model="password"
            required
            style="display:block;width:100%;padding:10px;margin:8px 0 16px"
        >

        @error('password')
            <p style="color:red">{{ $message }}</p>
        @enderror

        <label>
            <input type="checkbox" wire:model="remember">
            Ingat saya
        </label>

        <button
            id="login-submit"
            type="submit"
            style="display:block;margin-top:18px;padding:10px 20px"
        >
            Login
        </button>

    </form>

    <p>
        <a href="/register">Daftar akun</a>
    </p>

</div>
