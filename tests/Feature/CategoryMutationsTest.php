<?php

namespace Tests\Feature;

use App\Category;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CategoryMutationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_category()
    {
    	$user = factory(User::class)->create();

    	Passport::actingAs($user);

    	$response = $this->graphQL('
    		mutation {
    			createCategory(input: {
    				name: "Category 1"
				}) {
					name
					user {
						id
					}
				}
    		}
		');

		$response->assertJson([
			'data' => [
				'createCategory' => [
					'name' => 'Category 1',
					'user' => [
						'id' => $user->id
					]
				]
			]
		]);
    }

    public function test_it_can_update_a_category()
    {
    	$user = factory(User::class)->create();
    	$category = factory(Category::class)->create([
    		'user_id' => $user->id
    	]);

    	Passport::actingAs($user);

    	$response = $this->graphQL('
    		mutation {
    			updateCategory(id: "' . $category->id . '", input: {
    				name: "Category 1"
				}) {
					name
					user {
						id
					}
				}
    		}
		');

		$response->assertJson([
			'data' => [
				'updateCategory' => [
					'name' => 'Category 1',
					'user' => [
						'id' => $user->id
					]
				]
			]
		]);
    }

    public function test_it_cant_update_a_category_when_no_owner()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $category = factory(Category::class)->create([
    		'user_id' => $user->id
    	]);

        Passport::actingAs($user2);

        $response = $this->graphQL('
            mutation {
                updateCategory(id: ' . $category->id . ', input: {
                    name: "Savings"    
                }) {
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

        $this->assertDataBaseMissing('categories', [
            'id' => $category->id,
            'user_id' => $user->id,
            'name' => 'Savings'
        ]);
    }

    public function test_it_can_delete_a_category()
    {
        $user = factory(User::class)->create();
        $category = factory(Category::class)->create([
    		'user_id' => $user->id
    	]);

        Passport::actingAs($user);
        
        $response = $this->graphQL('
            mutation {
                deleteCategory(id: ' . $category->id . ') {
                	id
                	name
                }
            }
        ');

        $response->assertJson([
            'data' => [
                'deleteCategory' => [
                	'id' => $category->id,
                	'name' => $category->name
                ]
            ]
        ]);

        $this->assertNotNull($category->fresh()->deleted_at);
    }

    public function test_it_cant_delete_a_category_when_not_owner()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $category = factory(Category::class)->create([
    		'user_id' => $user->id
    	]);

        Passport::actingAs($user2);
        
        $response = $this->graphQL('
            mutation {
                deleteCategory(id: ' . $category->id . ') {
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
