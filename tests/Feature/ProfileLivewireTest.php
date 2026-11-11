<?php

namespace Tests\Feature;

use App\Features\Profile\Livewire\Profile;
use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_updates_without_password_or_photo(): void
    {
        $user = User::factory()->create(['name' => 'Lama']);
        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSet('form.name', 'Lama')
            ->set('form.name', 'Baru')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('notify');

        $this->assertSame('Baru', $user->fresh()->name);
    }

    public function test_profile_updates_password_and_replaces_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('form.password', 'passwordbaru1')
            ->set('photo', UploadedFile::fake()->image('a.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $first = $user->fresh()->profile_photo_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);
        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));

        Livewire::test(Profile::class)
            ->set('photo', UploadedFile::fake()->image('b.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($first);
    }

    public function test_profile_validation_fails(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors(['form.name']);
    }

    public function test_service_update_keeps_password_when_empty(): void
    {
        $user = User::factory()->create();
        $old = $user->password;

        (new ProfileService())->update($user, ['name' => 'X', 'password' => '']);

        $this->assertSame($old, $user->fresh()->password);
    }
}
