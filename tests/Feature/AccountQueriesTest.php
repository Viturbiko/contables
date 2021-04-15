<?php

namespace Tests\Feature;

use App\Account;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AccountQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queries_accounts()
    {
        $user = factory(User::class)->create();

        $accounts = factory(Account::class, 3)->create([
            'user_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->graphQL('
            query {
                accounts(first: 20) {
                    data {
                        id
                        name
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'accounts' => [
                    'data' => [],
                    'paginatorInfo' => []
                ]
            ]
        ]);
    }

    public function test_it_queries_an_account()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->graphQL('
            query {
                account(id: ' . $account->id . ') {
                    id
                    name
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'name' => $account->name,
                ]
            ]
        ]);
    }

    public function test_it_cant_query_an_account_not_owned()
    {
        $user = factory(User::class)->create();

        $account = factory(Account::class)->create([
            'user_id' => $user->id
        ]);

        $nonUserAccount = factory(Account::class)->create();

        Passport::actingAs($user);

        $response = $this->graphQL('
            query {
                account(id: ' . $nonUserAccount->id . ') {
                    id
                    name
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
    }
}
