<?php

namespace App\Traits;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

/**
 * Trait HasAddress
 *
 * @property ?Region $region
 * @property array $address
 */
trait HasAddress
{
    public ?Region $region = null;

    public function initializeHasAddressData(): void
    {
        $this->casts += ['address' => 'array'];
    }

    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value ?? '', true),
            set: fn ($value) => $this->parseAddress($value),
        );
    }

    private function parseAddress(?array $value): ?string
    {
        if (empty($value) || ! is_array($value)) {
            return null;
        }

        if (! empty($value['country_code'])) {
            $country = Country::firstWhere('code', Str::of($value['country_code'])->lower()->trim()->value());
        }

        if (empty($country) && ! empty($value['country'])) {
            $country = Country::firstWhere('name', Str::of($value['country'])->title()->trim()->value());
        }

        if (! empty($value['region_code'])) {
            $region = Region::firstWhere('code', Str::of($value['region_code'])->lower()->trim()->value());
        }

        if (empty($region) && ! empty($value['region'])) {
            $region = Region::firstWhere('name', Str::of($value['region'])->title()->trim()->value());
        }

        return json_encode([
            'country' => $country->name ?? $value['country'] ?? null,
            'country_code' => $country->code ?? $value['country_code'] ?? null,
            'region' => $region->name ?? $value['region'] ?? null,
            'region_code' => $region->code ?? $value['region_code'] ?? null,
            'city' => $value['city'] ?? null,
            'postcode' => $value['postcode'] ?? null,
            'line1' => $value['line1'] ?? null,
            'line2' => $value['line2'] ?? null,
            'longitude' => $value['longitude'] ?? null,
            'latitude' => $value['latitude'] ?? null,
        ]);
    }

    public function toStripeAddress(): array
    {
        return [
            'line1' => $this->address['line1'] ?? null,
            'line2' => $this->address['line2'] ?? null,
            'city' => $this->address['city'] ?? null,
            'state' => $this->address['region_code'] ?? null,
            'postal_code' => $this->address['postcode'] ?? null,
            'country' => Str::upper($this->address['country_code'] ?? null),
        ];
    }
}
