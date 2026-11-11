<?php

namespace Tests\Feature;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'member', 'username' => 'budi']);
        $this->actingAs($this->user);
    }

    public function test_page_renders_with_current_data(): void
    {
        $this->get('/profile')->assertOk();

        Livewire::test(Profile::class)->assertSet('form.username', 'budi');
    }

    public function test_update_without_password_keeps_password(): void
    {
        Livewire::test(Profile::class)
            ->set('form.name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $fresh = $this->user->fresh();
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertTrue(Hash::check('password', $fresh->password));
    }

    public function test_update_with_new_password(): void
    {
        Livewire::test(Profile::class)
            ->set('form.password', 'password-baru-123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('password-baru-123', $this->user->fresh()->password));
    }

    public function test_validation_and_unique_rules(): void
    {
        User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.test']);

        Livewire::test(Profile::class)
            ->set('form.username', 'dipakai')
            ->set('form.email', 'dipakai@example.test')
            ->set('form.password', 'x')
            ->call('save')
            ->assertHasErrors(['form.username', 'form.email', 'form.password']);
    }

    public function test_upload_photo_and_replace_it(): void
    {
        Storage::fake('public');

        $component = Livewire::test(Profile::class)
            ->set('photo', UploadedFile::fake()->image('a.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $first = $this->user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $component->set('photo', UploadedFile::fake()->image('b.jpg'))->call('save');

        $second = $this->user->fresh()->profile_photo_path;
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_admin_cannot_open_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/profile')->assertForbidden();
    }
}
