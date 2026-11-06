<?php

namespace Database\Factories;

use App\Interfaces\HasCommentsInterface;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $commentableModel = loadModels(HasCommentsInterface::class)->random();

        $className = get_class($commentableModel);

        return [
            'created_by' => associateTo(User::class),
            //        'comment_id' => $this->faker->boolean ? associateTo(\App\Comment::class) : null,
            'commentable_type' => $className,
            'commentable_id' => associateTo($className),
            'content' => $this->faker->sentence,
        ];
    }
}
