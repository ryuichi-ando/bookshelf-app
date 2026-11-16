<?php

namespace Tests\Feature\Fortify;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常な入力でユーザーを登録できる
     */
    public function test_user_can_be_created(): void
    {
        $action = new CreateNewUser();

        $user = $action->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * パスワードがハッシュ化されて保存される
     */
    public function test_password_is_hashed_when_user_is_created(): void
    {
        $action = new CreateNewUser();

        $user = $action->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertTrue(
            Hash::check('password123', $user->password)
        );

        $this->assertNotSame('password123', $user->password);
    }

    /**
     * 重複したメールアドレスでは登録できない
     */
    public function test_duplicate_email_cannot_be_used(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $action = new CreateNewUser();

        try {
            $action->create([
                'name' => '別のユーザー',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $this->fail('重複したメールアドレスが許可されました。');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey(
                'email',
                $e->errors()
            );
        }
    }

    /**
     * 不正なメールアドレスでは登録できない
     */
    public function test_invalid_email_cannot_be_used(): void
    {
        $action = new CreateNewUser();

        try {
            $action->create([
                'name' => 'テストユーザー',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $this->fail('不正なメールアドレスが許可されました。');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey(
                'email',
                $e->errors()
            );
        }
    }

    /**
     * パスワードと確認用パスワードが一致しない場合は登録できない
     */
    public function test_password_confirmation_must_match(): void
    {
        $action = new CreateNewUser();

        try {
            $action->create([
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ]);

            $this->fail('一致しないパスワードが許可されました。');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey(
                'password',
                $e->errors()
            );
        }
    }

    /**
     * 必須項目が不足している場合は登録できない
     */
    public function test_required_fields_are_validated(): void
    {
        $action = new CreateNewUser();

        try {
            $action->create([]);

            $this->fail('必須項目が不足しているのに登録されました。');
        } catch (ValidationException $e) {
            $errors = $e->errors();

            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
        }
    }
}
