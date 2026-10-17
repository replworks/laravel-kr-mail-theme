<?php

namespace Replworks\KrMailTheme;

final class BrandInformation
{
    public const SERVICE_NAME_PLACEHOLDER = '[서비스명]';

    public const LOGO_PLACEHOLDER = '[로고]';

    private function __construct(
        private readonly ?string $serviceName,
        private readonly ?string $logo,
        private readonly ?string $companyName,
        private readonly ?string $customerCenter,
        private readonly ?string $businessRegistrationNumber,
        private readonly ?string $address,
        private readonly ?string $termsUrl,
    ) {}

    /**
     * @param  array<string, mixed>|null  $config
     */
    public static function fromConfig(?array $config = null): self
    {
        $config ??= (array) config('kr-mail-theme', []);

        return new self(
            self::supplied($config['service_name'] ?? null),
            self::supplied($config['logo'] ?? null),
            self::supplied($config['company_name'] ?? null),
            self::supplied($config['customer_center'] ?? null),
            self::supplied($config['business_registration_number'] ?? null),
            self::supplied($config['address'] ?? null),
            self::supplied($config['terms_url'] ?? null),
        );
    }

    public function serviceName(): string
    {
        return $this->serviceName ?? self::SERVICE_NAME_PLACEHOLDER;
    }

    public function logo(): ?string
    {
        return $this->logo;
    }

    public function hasLogo(): bool
    {
        return $this->logo !== null;
    }

    public function companyName(): ?string
    {
        return $this->companyName;
    }

    public function customerCenter(): ?string
    {
        return $this->customerCenter;
    }

    public function businessRegistrationNumber(): ?string
    {
        return $this->businessRegistrationNumber;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function termsUrl(): ?string
    {
        return $this->termsUrl;
    }

    private static function supplied(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }
}
