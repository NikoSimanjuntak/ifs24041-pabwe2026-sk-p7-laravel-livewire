
<div style="max-width:440px;margin:40px auto;background:white;padding:28px;border-radius:12px">

    <h1>Register Member</h1>

    <form wire:submit="register">

        <label>Nama</label>
        <input type="text" wire:model="name" required>
        @error('name')
            <small style="color:red">{{ $message }}</small>
        @enderror

        <label>Username</label>
        <input type="text" wire:model="username" required>
        @error('username')
            <small style="color:red">{{ $message }}</small>
        @enderror

        <label>Email</label>
        <input type="email" wire:model="email" required>
        @error('email')
            <small style="color:red">{{ $message }}</small>
        @enderror

        <label>Password</label>
        <input type="password" wire:model="password" required>
        @error('password')
            <small style="color:red">{{ $message }}</small>
        @enderror

        <label>Konfirmasi Password</label>
        <input type="password" wire:model="password_confirmation" required>

        <button type="submit">Daftar</button>

    </form>

    <a href="/login">Kembali ke login</a>

</div>
