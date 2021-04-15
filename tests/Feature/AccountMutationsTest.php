<?php

namespace Tests\Feature;

use App\Account;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_account()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->graphQL('mutation {
            createAccount(input: {
                name: "wallet",
                balance: 200
            }) {
                name
                balance
                user {
                    id
                    name
                }
            }
        }');

        $response->assertJson([
            'data' => [
                'createAccount' => [
                    'name' => 'wallet',
                    'balance' => 200.00,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('accounts', [
            'name' => 'wallet',
            'balance' => '200',
            'user_id' => $user->id
        ]);
    }

    public function test_it_can_update_an_account()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $account = factory(Account::class)->create([
            'name' => 'wallet',
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $response = $this->graphQL('
            mutation {
                updateAccount(id: ' . $account->id . ', input: {
                    name: "Savings"    
                }) {
                    id
                    name
                    balance
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'updateAccount' => [
                    'id' => $account->id,
                    'name' => "Savings",
                    'balance' => $account->balance

                ]
            ]
        ]);

        $this->assertDataBaseHas('accounts', [
            'id' => $account->id,
            'user_id' => $user->id,
            'name' => 'Savings'
        ]);
    }

    public function test_it_cant_update_an_account_when_no_owner()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        Passport::actingAs($user2);

        $account = factory(Account::class)->create([
            'name' => 'wallet',
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $response = $this->graphQL('
            mutation {
                updateAccount(id: ' . $account->id . ', input: {
                    name: "Savings"    
                }) {
                    id
                    name
                    balance
                }
            }
        ');

        $response->assertJson([
            'errors' => [
                [
                    'message' => "This action is unauthorized."
                ]
            ]
        ]);

        $this->assertDataBaseMissing('accounts', [
            'id' => $account->id,
            'user_id' => $user->id,
            'name' => 'Savings'
        ]);
    }
}
