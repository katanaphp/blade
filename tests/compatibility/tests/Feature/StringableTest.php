<?php

namespace Tests\Feature;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Blade;
use Tests\Support\ComparesWithKatana;
use Tests\TestCase;

class StringableTest extends TestCase
{
    use ComparesWithKatana;

    public function testStringableWorksWithEscapedAndUnscapedOutput(): void
    {
        $output = "<b>Bold</b>";

        $this->stringable(fn(User $_) => $output);

        $this->assertRendersSame('{{ $user }}', ['user' => new User]);

        $this->assertRendersSame('{!! $user !!}', ['user' => new User]);
    }

    /**
     * Laravel only: Katana registers stringables by the
     * closure's type hint and has no class string form.
     */
    public function testWithClassString(): void
    {
        $output = "I am always, a user";

        Blade::stringable(User::class, fn() => $output);

        $this->assertSame(
            $output,
            Blade::render("{{ \$user }}", ['user' => new User])
        );
    }

    public function testMultipleStringableRegistrationUsesLast(): void
    {
        Blade::stringable(User::class, fn() => 'output 1');
        Blade::stringable(User::class, fn() => 'output 2');

        $this->assertSame(
            'output 2',
            Blade::render("{{ \$user }}", ['user' => new User])
        );
    }

    public function testWithInterfaceDoesNotInterceptClass(): void
    {
        $output = "Interface will output class";

        Blade::stringable(Authenticatable::class, fn() => $output);

        $user = new User();

        $this->assertTrue($user instanceof Authenticatable);
        $this->assertSame(
            '[]',
            Blade::render('{{ $user }}', ['user' => $user])
        );
    }

    public function testClassStringWithoutHandler(): void
    {
        Blade::stringable(User::class);

        $this->assertSame(
            '[]',
            Blade::render('{{ $user }}', ['user' => new User()])
        );
    }

    public function testClosureWithClass(): void
    {
        $output = "I am a user";
        $this->stringable(fn(User $user) => $output);

        $this->assertRendersSame('{{ $user }}', ['user' => new User()]);
    }

    public function testStringableUsesLastRegisteredClosure(): void
    {
        $this->stringable(fn(User $_) => 'First user');
        $this->stringable(fn(User $_) => 'Second user');

        $this->assertRendersSame('{{ $user }}', ['user' => new User]);
    }


    public function testClosureWithInterfaceDoesNotInterceptClass(): void
    {
        $output = "Interface will output class";

        $this->stringable(fn(Authenticatable $_) => $output);

        $this->assertRendersSame('{{ $user }}', ['user' => new User()]);
    }

    public function testClosureWithIntersectionTypesAreNotSupported(): void
    {
        $this->assertThrowsSame(
            fn($blade) => $blade->stringable(fn(Authenticatable|Controller $_) => '')
        );
    }
}
