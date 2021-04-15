<?php

namespace Tests\Feature;

use App\Account;
use App\Category;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionMutationsTest extends TestCase
{
    use RefreshDatabase;

    function test_it_creates_transaction_and_updates_account_balance()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 0
        ]);

        $category = factory(Category::class)->create([
            'user_id' => $user->id
        ]);

        $response = $this->graphQL('
            mutation {
                createTransaction(input: {
                    account_id: ' . $account->id . ',
                    category_id: ' . $category->id . ',
                    amount: 100
                    type: INCOME,
                    description: "Income",
                }) {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'createTransaction' => [
                    'amount' => 100.0,
                    'type' => 'INCOME',
                    'description' => 'Income',
                    'account' => [
                        'id' => $account->id,
                        'name' => $account->name,
                        'balance' => 100.0
                    ]
                ]
            ]
        ]);
    }

    function test_it_creates_transaction_and_updates_account_balance_with_expense()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $category = factory(Category::class)->create([
            'user_id' => $user->id
        ]);

        $response = $this->graphQL('
            mutation {
                createTransaction(input: {
                    account_id: ' . $account->id . ',
                    category_id: ' . $category->id . ',
                    amount: 50
                    type: EXPENSE,
                    description: "Expense",
                }) {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'createTransaction' => [
                    'amount' => 50.0,
                    'type' => 'EXPENSE',
                    'description' => 'Expense',
                    'account' => [
                        'id' => $account->id,
                        'name' => $account->name,
                        'balance' => 50
                    ]
                ]
            ]
        ]);
    }

    public function test_it_can_update_an_income_transaction()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $transaction = factory(Transaction::class)->state('income')->create([
            'account_id' => $account->id,
            'amount' => 50
        ]);

        $this->assertEquals(150, $account->fresh()->balance);

        $response = $this->graphQL('
            mutation {
                updateTransaction(id: ' . $transaction->id .  ' input: {
                    amount: 20
                }) {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'updateTransaction' => [
                    'amount' => 20.00,
                    'account' => [
                        'balance' => 120.00
                    ]
                ]
            ]
        ]);
    }

    public function test_it_can_update_a_expense_transaction()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $transaction = factory(Transaction::class)->state('expense')->create([
            'account_id' => $account->id,
            'amount' => 50
        ]);

        $this->assertEquals(50, $account->fresh()->balance);

        $response = $this->graphQL('
            mutation {
                updateTransaction(id: ' . $transaction->id .  ' input: {
                    amount: 20
                }) {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'updateTransaction' => [
                    'amount' => 20.00,
                    'account' => [
                        'balance' => 80.00
                    ]
                ]
            ]
        ]);
    }

    public function test_it_cant_update_a_transaction_when_not_owner()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        Passport::actingAs($user2);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $transaction = factory(Transaction::class)->state('expense')->create([
            'account_id' => $account->id,
            'amount' => 50
        ]);

        $this->assertEquals(50, $account->fresh()->balance);

        $response = $this->graphQL('
            mutation {
                updateTransaction(id: ' . $transaction->id .  ' input: {
                    amount: 20
                }) {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
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

    public function test_it_can_delete_a_expense_transaction()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $transaction = factory(Transaction::class)->state('expense')->create([
            'account_id' => $account->id,
            'amount' => 50
        ]);

        $this->assertEquals(50, $account->fresh()->balance);

        $response = $this->graphQL('
            mutation {
                deleteTransaction(id: ' . $transaction->id . ') {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'deleteTransaction' => [
                    'account' => [
                        'balance' => 100.00
                    ]
                ]
            ]
        ]);
    }

    public function test_it_can_delete_an_income_transaction()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);
        
        $account = factory(Account::class)->create([
            'user_id' => $user->id,
            'balance' => 100
        ]);

        $transaction = factory(Transaction::class)->state('income')->create([
            'account_id' => $account->id,
            'amount' => 50
        ]);

        $this->assertEquals(150, $account->fresh()->balance);

        $response = $this->graphQL('
            mutation {
                deleteTransaction(id: ' . $transaction->id . ') {
                    amount
                    type
                    description
                    account {
                        id
                        name
                        balance
                    }
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'deleteTransaction' => [
                    'account' => [
                        'balance' => 100.00
                    ]
                ]
            ]
        ]);
    }
}
