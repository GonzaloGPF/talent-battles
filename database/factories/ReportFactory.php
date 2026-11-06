<?php

namespace Database\Factories;

use App\Enums\ReportStates;
use App\Interfaces\HasReportsInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $reportableModel = loadModels(HasReportsInterface::class)->random();

        $className = get_class($reportableModel);

        $isFinished = $this->faker->boolean();

        if ($isFinished) {
            $reportState = $this->faker->randomElement(ReportStates::getFinishedStates());
            $resolution = $this->faker->sentence();
            $closedAt = $this->faker->dateTimeBetween('-1 year');
        } else {
            $reportState = $this->faker->randomElement(ReportStates::getPendingStates());
        }

        return [
            'created_by' => associateTo(User::class),
            'report_state' => $reportState,
            'message' => $this->faker->sentence(),
            'resolution' => $resolution ?? null,
            'reportable_type' => $className,
            'reportable_id' => associateTo($className),
            'closed_at' => $closedAt ?? null,
        ];
    }

    public function pending(): ReportFactory
    {
        return $this->state([
            'report_state' => $this->faker->randomElement(ReportStates::getPendingStates()),
            'resolution' => null,
        ]);
    }

    public function finished(): ReportFactory
    {
        return $this->state([
            'report_state' => $this->faker->randomElement(ReportStates::getFinishedStates()),
            'resolution' => $this->faker->dateTimeBetween('-1 year'),
        ]);
    }
}
